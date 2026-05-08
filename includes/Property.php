<?php
/**
 * Property Model Class
 * Handles all property-related database operations
 */

class Property {
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_SOLD = 'sold';
    public const STATUS_RENTED = 'rented';

    public const PUBLIC_STATUSES = [self::STATUS_ACTIVE];
    public const ADMIN_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_DRAFT,
        self::STATUS_PENDING,
        self::STATUS_SOLD,
        self::STATUS_RENTED,
    ];

    public const LISTING_STATUSES = ['sale', 'rent', 'sold', 'pending'];

    private $db;
    private $errors = [];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public static function normalizeWorkflowStatus($status) {
        $status = strtolower(trim((string) $status));

        if ($status === 'featured') {
            return self::STATUS_ACTIVE;
        }

        return in_array($status, self::ADMIN_STATUSES, true)
            ? $status
            : self::STATUS_PENDING;
    }

    public static function isPublicStatus($status) {
        return in_array((string) $status, self::PUBLIC_STATUSES, true);
    }

    /**
     * Public listing search WITH pagination fallback when strict filters yield zero rows.
     *
     * @param array<string,mixed> $filters Normalized listing filters from listing_filters_from_request()
     * @return array{
     *   properties: array,
     *   total: int,
     *   effective_filters: array,
     *   fallback_message: ?string,
     *   requested_filters: array
     * }
     */
    public function searchListingsPaginated(array $filters, $page = 1, $perPage = null) {
        $page    = max(1, (int) $page);
        $perPage = $perPage !== null ? max(1, (int) $perPage) : ITEMS_PER_PAGE;

        $strictCount = $this->getTotalCount($filters);

        if ($strictCount > 0) {
            $totalPages = max(1, (int) ceil($strictCount / $perPage));
            $page       = min($page, $totalPages);

            return [
                'properties'         => $this->getAll($filters, $page, $perPage),
                'total'               => $strictCount,
                'effective_filters'   => $filters,
                'fallback_message'    => null,
                'requested_filters'   => $filters,
            ];
        }

        foreach ($this->nearbyListingFallbackVariants($filters) as $tier) {
            $relaxed      = $tier['filters'];
            $relaxedCount = $this->getTotalCount($relaxed);

            if ($relaxedCount > 0) {
                $totalPages = max(1, (int) ceil($relaxedCount / $perPage));
                $page       = min($page, $totalPages);

                return [
                    'properties'         => $this->getAll($relaxed, $page, $perPage),
                    'total'               => $relaxedCount,
                    'effective_filters'   => $relaxed,
                    'fallback_message'    => $tier['message'],
                    'requested_filters'   => $filters,
                ];
            }
        }

        return [
            'properties'         => [],
            'total'               => 0,
            'effective_filters'   => $filters,
            'fallback_message'    => null,
            'requested_filters'   => $filters,
        ];
    }

    /**
     * Build progressively relaxed filters for nearby / broader results.
     *
     * @param array<string,mixed> $filters
     * @return array<int,array{filters: array, message: string}>
     */
    private function nearbyListingFallbackVariants(array $filters) {
        $tiers       = [];
        $signatures = [];

        $freeze = static function (array $f): string {
            ksort($f);

            return md5(json_encode($f));
        };

        $push = function (array $f, $message) use (&$tiers, &$signatures, $freeze) {
            $sig = $freeze($f);
            if (isset($signatures[$sig])) {
                return;
            }
            $signatures[$sig] = true;
            $tiers[] = [
                'filters' => $f,
                'message' => $message,
            ];
        };

        if (!empty($filters['city'])) {
            $f = $filters;
            unset($f['city']);
            $push($f, 'No exact matches found in this area. Showing nearby properties in the broader region instead.');
        }

        if (!empty($filters['keyword'])) {
            $f = $filters;
            unset($f['keyword'], $f['city']);
            $push($f, 'No exact phrase matches on the map. Showing nearby listings that still match your other filters.');
        }

        if ((!empty($filters['geo_lat']) && !empty($filters['geo_lng']))) {
            $curRadius = isset($filters['geo_radius_mi']) ? (float) $filters['geo_radius_mi'] : 75.0;

            foreach ([160.0, 280.0] as $widenTo) {
                if ($widenTo <= $curRadius + 1) {
                    continue;
                }

                $f = $filters;
                unset($f['keyword'], $f['city']);
                $f['geo_radius_mi'] = $widenTo;
                $push($f, 'No homes right next to your pin. Showing a wider nearby search instead.');
            }
        }

        if (!empty($filters['state']) && (!empty($filters['keyword']) || !empty($filters['city']) || (!empty($filters['geo_lat']) && !empty($filters['geo_lng'])))) {
            $f = $filters;
            unset($f['keyword'], $f['city'], $f['geo_lat'], $f['geo_lng'], $f['geo_radius_mi']);
            $push($f, 'Showing everything available in your selected state that still meets your listing filters.');
        }

        if (!empty($filters['state'])) {
            $f = $filters;
            unset($f['state'], $f['keyword'], $f['city'], $f['geo_lat'], $f['geo_lng'], $f['geo_radius_mi']);
            $push($f, 'Showing similar listings nationally with your sale/rent & budget filters.');
        }

        return $tiers;
    }

    /**
     * PDO requires unique named placeholders when ATTR_EMULATE_PREPARES is false.
     *
     * @param array<string,mixed> $filters
     * @return array{0: string[], 1: array<string,mixed>}
     */
    private function compilePublicListingConditions(array $filters) {
        $where  = [];
        $params = [];

        $where[] = 'p.property_status = :fdb_ps';

        // Back-compat: callers may send legacy key `status` for property_status workflow column.
        $inventory = $filters['inventory_status']
            ?? $filters['status']
            ?? 'active';
        $params['fdb_ps'] = $inventory;

        if (!empty($filters['keyword'])) {
            $term = trim((string) $filters['keyword']);
            if ($term !== '') {
                $like  = '%' . $term . '%';
                $cols  = ['title', 'description', 'address', 'city', 'state', 'zip_code'];
                $parts = [];
                foreach ($cols as $idx => $col) {
                    $ph        = 'fdb_kw' . $idx;
                    $parts[]   = 'p.' . $col . ' LIKE :' . $ph;
                    $params[$ph] = $like;
                }

                $where[] = '(' . implode(' OR ', $parts) . ')';
            }
        }

        if (!empty($filters['city'])) {
            $where[]      = 'p.city LIKE :fdb_citylike';
            $params['fdb_citylike'] = '%' . trim((string) $filters['city']) . '%';
        }

        if (!empty($filters['state'])) {
            $where[]           = 'p.state = :fdb_state_exact';
            $params['fdb_state_exact'] = strtoupper(trim((string) $filters['state']));
        }

        if (!empty($filters['property_type'])) {
            $where[]           = 'p.property_type_id = :fdb_pt';
            $params['fdb_pt'] = (int) $filters['property_type'];
        }

        if (!empty($filters['status_type'])) {
            $st               = strtolower((string) $filters['status_type']) === 'rent' ? 'rent' : 'sale';
            $where[]           = 'p.status = :fdb_sale_rent';
            $params['fdb_sale_rent'] = $st;
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '' && $filters['min_price'] !== null) {
            $where[]            = 'p.price >= :fdb_pmin';
            $params['fdb_pmin'] = (float) $filters['min_price'];
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '' && $filters['max_price'] !== null) {
            $where[]             = 'p.price <= :fdb_pmax';
            $params['fdb_pmax'] = (float) $filters['max_price'];
        }

        if (isset($filters['bedrooms']) && $filters['bedrooms'] !== '' && $filters['bedrooms'] !== null) {
            $where[]               = 'p.bedrooms >= :fdb_bed';
            $params['fdb_bed'] = (int) $filters['bedrooms'];
        }

        if (isset($filters['bathrooms']) && $filters['bathrooms'] !== '' && $filters['bathrooms'] !== null) {
            $where[]                 = 'p.bathrooms >= :fdb_bath';
            $params['fdb_bath'] = (float) $filters['bathrooms'];
        }

        if (!empty($filters['agent_id'])) {
            $where[]               = 'p.agent_id = :fdb_ag';
            $params['fdb_ag'] = (int) $filters['agent_id'];
        }

        if (!empty($filters['featured_only'])) {
            $where[] = 'p.is_featured = 1';
        }

        if (!empty($filters['geo_lat']) && !empty($filters['geo_lng'])) {
            $lat = filter_var($filters['geo_lat'], FILTER_VALIDATE_FLOAT);
            $lng = filter_var($filters['geo_lng'], FILTER_VALIDATE_FLOAT);

            if ($lat !== false && $lng !== false && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                $miles = isset($filters['geo_radius_mi'])
                    ? (float) $filters['geo_radius_mi']
                    : 75.0;
                $miles = max(5.0, min(500.0, $miles));

                $dlat = $miles / 69.172;
                $dlng = $miles / (69.172 * max(cos(deg2rad($lat)), 0.2));

                $where[] = '(p.latitude IS NOT NULL AND p.longitude IS NOT NULL AND p.latitude BETWEEN :fdb_la0 AND :fdb_la1 AND p.longitude BETWEEN :fdb_lo0 AND :fdb_lo1)';
                $params['fdb_la0'] = $lat - $dlat;
                $params['fdb_la1'] = $lat + $dlat;
                $params['fdb_lo0'] = $lng - $dlng;
                $params['fdb_lo1'] = $lng + $dlng;
            }
        }

        return [$where, $params];
    }

    /**
     * Get all properties with filters
     *
     * @param array<string,mixed> $filters
     */
    public function getAll($filters = [], $page = 1, $perPage = ITEMS_PER_PAGE) {
        [$where, $params] = $this->compilePublicListingConditions($filters);

        $whereClause = implode(' AND ', $where);
        $offset      = ($page - 1) * $perPage;

        $sql = "SELECT p.*, 
                CONCAT(a.first_name, ' ', a.last_name) as agent_name,
                a.phone as agent_phone,
                a.avatar as agent_avatar,
                a.email as agent_email,
                pt.name as property_type_name,
                c.name as category_name,
                (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, display_order ASC, id ASC LIMIT 1) as primary_image
                FROM properties p
                LEFT JOIN agents a ON p.agent_id = a.id
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE {$whereClause}
                ORDER BY p.is_featured DESC, p.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get property by ID
     */
    public function getById($id) {
        $sql = "SELECT p.*, 
                CONCAT(a.first_name, ' ', a.last_name) as agent_name,
                a.email as agent_email,
                a.phone as agent_phone,
                a.mobile as agent_mobile,
                a.avatar as agent_avatar,
                a.bio as agent_bio,
                a.license_number as agent_license,
                a.years_experience as agent_experience,
                pt.name as property_type_name,
                c.name as category_name
                FROM properties p
                LEFT JOIN agents a ON p.agent_id = a.id
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = :id";

        return $this->db->query($sql, ['id' => $id])->fetch();
    }

    /**
     * Get property by slug
     */
    public function getBySlug($slug, $publicOnly = false) {
        $sql = "SELECT p.*, 
                CONCAT(a.first_name, ' ', a.last_name) as agent_name,
                a.email as agent_email,
                a.phone as agent_phone,
                a.mobile as agent_mobile,
                a.avatar as agent_avatar,
                a.bio as agent_bio,
                a.license_number as agent_license,
                a.years_experience as agent_experience,
                pt.name as property_type_name,
                c.name as category_name
                FROM properties p
                LEFT JOIN agents a ON p.agent_id = a.id
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.slug = :slug";

        $params = ['slug' => $slug];

        if ($publicOnly) {
            $sql .= " AND p.property_status = :public_status";
            $params['public_status'] = self::STATUS_ACTIVE;
        }

        return $this->db->query($sql, $params)->fetch();
    }

    /**
     * Get featured properties
     */
    public function getFeatured($limit = 6) {
        $sql = "SELECT p.*, 
                CONCAT(a.first_name, ' ', a.last_name) as agent_name,
                a.phone as agent_phone,
                a.avatar as agent_avatar,
                pt.name as property_type_name,
                (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, display_order ASC, id ASC LIMIT 1) as primary_image
                FROM properties p
                LEFT JOIN agents a ON p.agent_id = a.id
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.is_featured = 1 AND p.property_status = 'active'
                ORDER BY p.created_at DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get latest properties
     */
    public function getLatest($limit = 6) {
        $sql = "SELECT p.*, 
                CONCAT(a.first_name, ' ', a.last_name) as agent_name,
                a.phone as agent_phone,
                a.avatar as agent_avatar,
                pt.name as property_type_name,
                (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, display_order ASC, id ASC LIMIT 1) as primary_image
                FROM properties p
                LEFT JOIN agents a ON p.agent_id = a.id
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.property_status = 'active'
                ORDER BY p.created_at DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get property images
     */
    public function getImages($propertyId) {
        return $this->db->select(
            'property_images',
            '*',
            'property_id = :property_id',
            ['property_id' => $propertyId],
            'is_primary DESC, display_order ASC, id ASC'
        );
    }

    public function getImageById($imageId, $propertyId = null) {
        $where = 'id = :id';
        $params = ['id' => (int) $imageId];

        if ($propertyId !== null) {
            $where .= ' AND property_id = :property_id';
            $params['property_id'] = (int) $propertyId;
        }

        return $this->db->selectOne('property_images', '*', $where, $params);
    }

    public function getPrimaryImage($propertyId) {
        $sql = "SELECT * FROM property_images
                WHERE property_id = :property_id
                ORDER BY is_primary DESC, display_order ASC, id ASC
                LIMIT 1";

        return $this->db->query($sql, ['property_id' => (int) $propertyId])->fetch();
    }

    /**
     * Get total count with filters
     *
     * @param array<string,mixed> $filters
     */
    public function getTotalCount($filters = []) {
        [$where, $params] = $this->compilePublicListingConditions($filters);
        $whereClause      = implode(' AND ', $where);
        $sql              = "SELECT COUNT(*) FROM properties p WHERE {$whereClause}";

        return $this->db->query($sql, $params)->fetchColumn();
    }

    /**
     * Platform inventory snapshot for Market Reports (active listings only).
     *
     * @return array<string, mixed>
     */
    public function getMarketSnapshotTotals() {
        $sql = "SELECT COUNT(*) AS total_active,
                COALESCE(SUM(CASE WHEN status = 'sale' THEN 1 ELSE 0 END), 0) AS sale_count,
                COALESCE(SUM(CASE WHEN status = 'rent' THEN 1 ELSE 0 END), 0) AS rent_count,
                AVG(CASE WHEN status = 'sale' THEN price END) AS avg_sale_price,
                AVG(CASE WHEN status = 'rent' THEN price END) AS avg_rent_price,
                MIN(CASE WHEN status = 'sale' THEN price END) AS min_sale_price,
                MAX(CASE WHEN status = 'sale' THEN price END) AS max_sale_price,
                MIN(CASE WHEN status = 'rent' THEN price END) AS min_rent_price,
                MAX(CASE WHEN status = 'rent' THEN price END) AS max_rent_price
            FROM properties
            WHERE property_status = 'active'";

        $row = $this->db->query($sql)->fetch();

        return is_array($row) ? $row : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMarketStatsByState($limit = 15) {
        $limit = max(1, min(50, (int) $limit));
        $sql = "SELECT state,
                COUNT(*) AS listing_count,
                COALESCE(SUM(CASE WHEN status = 'sale' THEN 1 ELSE 0 END), 0) AS sale_count,
                COALESCE(SUM(CASE WHEN status = 'rent' THEN 1 ELSE 0 END), 0) AS rent_count,
                AVG(CASE WHEN status = 'sale' THEN price END) AS avg_sale_price,
                AVG(CASE WHEN status = 'rent' THEN price END) AS avg_rent_price
            FROM properties
            WHERE property_status = 'active'
              AND state IS NOT NULL
              AND TRIM(state) <> ''
            GROUP BY state
            ORDER BY listing_count DESC
            LIMIT {$limit}";

        $rows = $this->db->query($sql)->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMarketStatsByMetro($limit = 12) {
        $limit = max(1, min(50, (int) $limit));
        $sql = "SELECT TRIM(city) AS city,
                TRIM(state) AS state,
                COUNT(*) AS listing_count,
                COALESCE(SUM(CASE WHEN status = 'sale' THEN 1 ELSE 0 END), 0) AS sale_count,
                COALESCE(SUM(CASE WHEN status = 'rent' THEN 1 ELSE 0 END), 0) AS rent_count,
                AVG(CASE WHEN status = 'sale' THEN price END) AS avg_sale_price,
                AVG(CASE WHEN status = 'rent' THEN price END) AS avg_rent_price
            FROM properties
            WHERE property_status = 'active'
              AND city IS NOT NULL
              AND TRIM(city) <> ''
              AND state IS NOT NULL
              AND TRIM(state) <> ''
            GROUP BY TRIM(city), TRIM(state)
            ORDER BY listing_count DESC
            LIMIT {$limit}";

        $rows = $this->db->query($sql)->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * Normalize and validate property payloads before insert/update.
     *
     * @param array<string,mixed> $data
     * @return array<string,mixed>|false
     */
    private function normalizePropertyData(array $data, $isCreate) {
        $required = ['title', 'description', 'price', 'address', 'city', 'state'];
        if ($isCreate) {
            $required[] = 'agent_id';
        }

        foreach ($required as $field) {
            if (!array_key_exists($field, $data) || trim((string) $data[$field]) === '') {
                $this->errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
            }
        }

        $trimFields = [
            'title', 'description', 'short_description', 'address', 'city', 'state',
            'zip_code', 'country', 'map_address', 'virtual_tour_url', 'video_url',
            'floor_plan', 'featured_image', 'admin_notes', 'slug',
        ];
        foreach ($trimFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $data[$field] = trim((string) $data[$field]);
            }
        }

        if (array_key_exists('state', $data)) {
            $state = strtoupper((string) $data['state']);
            $data['state'] = strlen($state) === 2 ? $state : trim((string) $data['state']);
        }

        if (array_key_exists('price', $data)) {
            if (!is_numeric($data['price']) || (float) $data['price'] < 0) {
                $this->errors[] = 'Price must be a valid non-negative number';
            } else {
                $data['price'] = (float) $data['price'];
            }
        }

        $numericFields = [
            'bedrooms' => 'int',
            'bathrooms' => 'float',
            'area_sqft' => 'float',
            'lot_size' => 'float',
            'parking_spaces' => 'int',
            'garage_spaces' => 'int',
            'floors' => 'int',
            'latitude' => 'float',
            'longitude' => 'float',
            'view_count' => 'int',
            'inquiry_count' => 'int',
        ];
        foreach ($numericFields as $field => $type) {
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                continue;
            }

            if (!is_numeric($data[$field])) {
                $this->errors[] = ucfirst(str_replace('_', ' ', $field)) . ' must be numeric';
                continue;
            }

            if (in_array($field, ['latitude', 'longitude'], true)) {
                $data[$field] = (float) $data[$field];
            } else {
                $data[$field] = $type === 'int' ? max(0, (int) $data[$field]) : max(0, (float) $data[$field]);
            }
        }

        foreach (['agent_id', 'category_id', 'property_type_id', 'approved_by'] as $idField) {
            if (array_key_exists($idField, $data) && $data[$idField] !== null && $data[$idField] !== '') {
                $data[$idField] = (int) $data[$idField];
            }
        }

        if (array_key_exists('year_built', $data) && $data['year_built'] !== null && $data['year_built'] !== '') {
            $year = (int) $data['year_built'];
            if ($year < 1800 || $year > ((int) date('Y') + 2)) {
                $this->errors[] = 'Year built is outside the allowed range';
            } else {
                $data['year_built'] = $year;
            }
        }

        if (array_key_exists('status', $data)) {
            $data['status'] = strtolower((string) $data['status']);
            if (!in_array($data['status'], self::LISTING_STATUSES, true)) {
                $this->errors[] = 'Listing type must be sale or rent';
            }
        }

        if (array_key_exists('property_status', $data)) {
            $incomingStatus = strtolower(trim((string) $data['property_status']));
            if ($incomingStatus === 'featured') {
                $data['is_featured'] = 1;
            }
            $data['property_status'] = self::normalizeWorkflowStatus($incomingStatus);
        } elseif ($isCreate) {
            $data['property_status'] = self::STATUS_PENDING;
        }

        foreach (['is_featured', 'is_premium'] as $boolField) {
            if (array_key_exists($boolField, $data)) {
                $data[$boolField] = !empty($data[$boolField]) ? 1 : 0;
            }
        }

        foreach (['features', 'amenities', 'nearby_places'] as $jsonField) {
            if (array_key_exists($jsonField, $data) && is_array($data[$jsonField])) {
                $clean = array_values(array_filter(array_map(static function ($item) {
                    return trim((string) $item);
                }, $data[$jsonField])));
                $data[$jsonField] = !empty($clean) ? json_encode($clean) : null;
            }
        }

        if (!empty($this->errors)) {
            return false;
        }

        return $data;
    }

    /**
     * Create new property
     */
    public function create($data) {
        $this->errors = [];

        $data = $this->normalizePropertyData($data, true);
        if ($data === false) {
            return false;
        }

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = generate_slug($data['title']);
            
            // Check if slug exists
            $existing = $this->getBySlug($data['slug']);
            if ($existing) {
                $data['slug'] .= '-' . uniqid();
            }
        }

        // Set default values
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Insert property
        $propertyId = $this->db->insert('properties', $data);

        return $propertyId;
    }

    /**
     * Update property
     */
    public function update($id, $data) {
        $this->errors = [];

        // Check if property exists
        $property = $this->getById($id);
        if (!$property) {
            $this->errors[] = "Property not found";
            return false;
        }

        $data = $this->normalizePropertyData($data, false);
        if ($data === false) {
            return false;
        }

        // Update slug if title changed
        if (!empty($data['title']) && $data['title'] !== $property['title']) {
            $data['slug'] = generate_slug($data['title']);
            
            // Check if slug exists
            $existing = $this->getBySlug($data['slug']);
            if ($existing && $existing['id'] != $id) {
                $data['slug'] .= '-' . uniqid();
            }
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->update('properties', $data, 'id = :id', ['id' => $id]);

        return true;
    }

    /**
     * Delete property
     */
    public function delete($id) {
        $property = $this->getById($id);
        if (!$property) {
            $this->errors[] = "Property not found";
            return false;
        }

        // Get property images
        $images = $this->getImages($id);
        
        $this->db->beginTransaction();
        try {
            // Delete property (cascade will delete images from database)
            $this->db->delete('properties', 'id = :id', ['id' => $id]);
            $this->db->commit();
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            $this->errors[] = "Unable to delete property";
            return false;
        }

        // Delete image files after database delete succeeds.
        $upload = new Upload();
        foreach ($images as $image) {
            $upload->deleteFile(UPLOAD_PATH . 'properties/' . $image['image_path']);
            if ($image['thumbnail_path']) {
                $upload->deleteFile(UPLOAD_PATH . 'thumbnails/' . $image['thumbnail_path']);
            }
        }

        return true;
    }

    /**
     * Add property image
     */
    public function addImage($propertyId, $imageData, $isPrimary = false) {
        if (!$this->getById($propertyId)) {
            $this->errors[] = "Property not found";
            return false;
        }

        if (!$isPrimary && !$this->getPrimaryImage($propertyId)) {
            $isPrimary = true;
        }

        // If this is primary, unset other primary images
        if ($isPrimary) {
            $this->db->update('property_images', ['is_primary' => 0], 'property_id = :property_id', ['property_id' => $propertyId]);
        }

        $data = [
            'property_id' => $propertyId,
            'image_path' => $imageData['filename'],
            'thumbnail_path' => $imageData['thumbnail'] ? basename($imageData['thumbnail']) : null,
            'is_primary' => $isPrimary ? 1 : 0,
            'display_order' => $this->getNextImageOrder($propertyId)
        ];

        return $this->db->insert('property_images', $data);
    }

    /**
     * Delete property image
     */
    public function deleteImage($imageId, $propertyId = null) {
        $image = $this->getImageById($imageId, $propertyId);
        
        if ($image) {
            $wasPrimary = !empty($image['is_primary']);

            $this->db->beginTransaction();
            try {
                $this->db->delete('property_images', 'id = :id', ['id' => $imageId]);

                if ($wasPrimary) {
                    $replacement = $this->db->query(
                        "SELECT id FROM property_images
                         WHERE property_id = :property_id
                         ORDER BY display_order ASC, id ASC
                         LIMIT 1",
                        ['property_id' => $image['property_id']]
                    )->fetch();

                    if ($replacement) {
                        $this->db->update('property_images', ['is_primary' => 1], 'id = :id', ['id' => $replacement['id']]);
                    }
                }

                $this->db->commit();
            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollback();
                }
                $this->errors[] = "Unable to delete image";
                return false;
            }

            $upload = new Upload();
            $upload->deleteFile(UPLOAD_PATH . 'properties/' . $image['image_path']);
            if ($image['thumbnail_path']) {
                $upload->deleteFile(UPLOAD_PATH . 'thumbnails/' . $image['thumbnail_path']);
            }
        }

        return true;
    }

    /**
     * Set primary image
     */
    public function setPrimaryImage($propertyId, $imageId) {
        $image = $this->getImageById($imageId, $propertyId);
        if (!$image) {
            $this->errors[] = "Image not found";
            return false;
        }

        $this->db->beginTransaction();
        try {
            // Unset all primary images
            $this->db->update('property_images', ['is_primary' => 0], 'property_id = :property_id', ['property_id' => $propertyId]);
            
            // Set new primary
            $this->db->update('property_images', ['is_primary' => 1], 'id = :id', ['id' => $imageId]);
            $this->db->commit();
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            $this->errors[] = "Unable to set featured image";
            return false;
        }

        return true;
    }

    /**
     * Reorder a property's gallery images.
     *
     * @param array<int|string,int|string> $orders image_id => display_order
     */
    public function reorderImages($propertyId, array $orders) {
        $propertyId = (int) $propertyId;
        if (!$this->getById($propertyId)) {
            $this->errors[] = "Property not found";
            return false;
        }

        $this->db->beginTransaction();
        try {
            foreach ($orders as $imageId => $order) {
                $imageId = (int) $imageId;
                $order = max(0, (int) $order);
                if ($imageId <= 0) {
                    continue;
                }

                $this->db->update(
                    'property_images',
                    ['display_order' => $order],
                    'id = :id AND property_id = :property_id',
                    ['id' => $imageId, 'property_id' => $propertyId]
                );
            }
            $this->db->commit();
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            $this->errors[] = "Unable to reorder images";
            return false;
        }

        return true;
    }

    /**
     * Update admin workflow status.
     */
    public function updateStatus($propertyId, $status, $adminId = null) {
        $propertyId = (int) $propertyId;
        $status = self::normalizeWorkflowStatus($status);

        if (!$this->getById($propertyId)) {
            $this->errors[] = "Property not found";
            return false;
        }

        $data = [
            'property_status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($status === self::STATUS_ACTIVE) {
            $data['approved_at'] = date('Y-m-d H:i:s');
            if ($adminId !== null) {
                $data['approved_by'] = (int) $adminId;
            }
        }

        $this->db->update('properties', $data, 'id = :id', ['id' => $propertyId]);

        return true;
    }

    /**
     * Increment view count
     */
    public function incrementViews($id) {
        $this->db->query("UPDATE properties SET view_count = view_count + 1 WHERE id = :id", ['id' => $id]);
    }

    /**
     * Get similar properties
     */
    public function getSimilar($property, $limit = 4) {
        $sql = "SELECT p.*, 
                CONCAT(a.first_name, ' ', a.last_name) as agent_name,
                a.phone as agent_phone,
                a.avatar as agent_avatar,
                pt.name as property_type_name,
                (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, display_order ASC, id ASC LIMIT 1) as primary_image
                FROM properties p
                LEFT JOIN agents a ON p.agent_id = a.id
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.id != :id 
                AND p.property_status = 'active'
                AND (p.property_type_id = :property_type_id OR p.city = :city)
                ORDER BY p.is_featured DESC, p.created_at DESC
                LIMIT {$limit}";

        return $this->db->query($sql, [
            'id' => $property['id'],
            'property_type_id' => $property['property_type_id'],
            'city' => $property['city']
        ])->fetchAll();
    }

    /**
     * Get property types
     */
    public function getPropertyTypes() {
        return $this->db->select('property_types', '*', 'status = :status', ['status' => 'active'], 'display_order ASC');
    }

    /**
     * Get categories
     */
    public function getCategories() {
        return $this->db->select('categories', '*', 'status = :status', ['status' => 'active'], 'display_order ASC');
    }

    /**
     * Get states for filter
     */
    public function getStates() {
        $sql = "SELECT DISTINCT state FROM properties WHERE property_status = 'active' ORDER BY state ASC";
        $rows = $this->db->query($sql)->fetchAll();

        return array_values(array_filter(array_map(static function ($row) {
            if (is_array($row)) {
                return trim((string) ($row['state'] ?? ''));
            }

            return trim((string) $row);
        }, $rows)));
    }

    /**
     * Get cities for filter
     */
    public function getCities($state = null) {
        $where = "property_status = 'active'";
        $params = [];
        
        if ($state) {
            $where .= " AND state = :state";
            $params['state'] = $state;
        }
        
        $sql = "SELECT DISTINCT city FROM properties WHERE {$where} ORDER BY city ASC";
        $rows = $this->db->query($sql, $params)->fetchAll();

        return array_values(array_filter(array_map(static function ($row) {
            if (is_array($row)) {
                return trim((string) ($row['city'] ?? ''));
            }

            return trim((string) $row);
        }, $rows)));
    }

    /**
     * Get agent properties
     */
    public function getAgentProperties($agentId, $status = null) {
        $where = 'p.agent_id = :agent_id';
        $params = ['agent_id' => $agentId];

        if ($status) {
            $where .= ' AND p.property_status = :status';
            $params['status'] = $status;
        }

        $sql = "SELECT p.*,
                (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, display_order ASC, id ASC LIMIT 1) as primary_image
                FROM properties p
                WHERE {$where}
                ORDER BY p.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get next image order
     */
    private function getNextImageOrder($propertyId) {
        $sql = "SELECT MAX(display_order) FROM property_images WHERE property_id = :property_id";
        $max = $this->db->query($sql, ['property_id' => $propertyId])->fetchColumn();
        return ($max ?? 0) + 1;
    }

    /**
     * Get errors
     */
    public function getErrors() {
        return $this->errors;
    }
}
