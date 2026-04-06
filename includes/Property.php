<?php
/**
 * Property Model Class
 * Handles all property-related database operations
 */

class Property {
    private $db;
    private $errors = [];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all properties with filters
     */
    public function getAll($filters = [], $page = 1, $perPage = ITEMS_PER_PAGE) {
        $where = ['p.property_status = :status'];
        $params = ['status' => $filters['status'] ?? 'active'];

        // Apply filters
        if (!empty($filters['keyword'])) {
            $where[] = "(p.title LIKE :keyword OR p.description LIKE :keyword OR p.address LIKE :keyword OR p.city LIKE :keyword)";
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['city'])) {
            $where[] = "p.city LIKE :city";
            $params['city'] = '%' . $filters['city'] . '%';
        }

        if (!empty($filters['state'])) {
            $where[] = "p.state = :state";
            $params['state'] = $filters['state'];
        }

        if (!empty($filters['property_type'])) {
            $where[] = "p.property_type_id = :property_type";
            $params['property_type'] = $filters['property_type'];
        }

        if (!empty($filters['status_type'])) {
            $where[] = "p.status = :status_type";
            $params['status_type'] = $filters['status_type'];
        }

        if (!empty($filters['min_price'])) {
            $where[] = "p.price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = "p.price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        if (!empty($filters['bedrooms'])) {
            $where[] = "p.bedrooms >= :bedrooms";
            $params['bedrooms'] = $filters['bedrooms'];
        }

        if (!empty($filters['bathrooms'])) {
            $where[] = "p.bathrooms >= :bathrooms";
            $params['bathrooms'] = $filters['bathrooms'];
        }

        if (!empty($filters['agent_id'])) {
            $where[] = "p.agent_id = :agent_id";
            $params['agent_id'] = $filters['agent_id'];
        }

        // Build query
        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, 
                CONCAT(a.first_name, ' ', a.last_name) as agent_name,
                a.phone as agent_phone,
                a.avatar as agent_avatar,
                a.email as agent_email,
                pt.name as property_type_name,
                c.name as category_name,
                (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
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
    public function getBySlug($slug) {
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

        return $this->db->query($sql, ['slug' => $slug])->fetch();
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
                (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
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
                (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
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
            'is_primary DESC, display_order ASC'
        );
    }

    /**
     * Get total count with filters
     */
    public function getTotalCount($filters = []) {
        $where = ['p.property_status = :status'];
        $params = ['status' => $filters['status'] ?? 'active'];

        if (!empty($filters['keyword'])) {
            $where[] = "(p.title LIKE :keyword OR p.description LIKE :keyword OR p.address LIKE :keyword OR p.city LIKE :keyword)";
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['city'])) {
            $where[] = "p.city LIKE :city";
            $params['city'] = '%' . $filters['city'] . '%';
        }

        if (!empty($filters['state'])) {
            $where[] = "p.state = :state";
            $params['state'] = $filters['state'];
        }

        if (!empty($filters['property_type'])) {
            $where[] = "p.property_type_id = :property_type";
            $params['property_type'] = $filters['property_type'];
        }

        if (!empty($filters['status_type'])) {
            $where[] = "p.status = :status_type";
            $params['status_type'] = $filters['status_type'];
        }

        if (!empty($filters['min_price'])) {
            $where[] = "p.price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = "p.price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        if (!empty($filters['bedrooms'])) {
            $where[] = "p.bedrooms >= :bedrooms";
            $params['bedrooms'] = $filters['bedrooms'];
        }

        if (!empty($filters['bathrooms'])) {
            $where[] = "p.bathrooms >= :bathrooms";
            $params['bathrooms'] = $filters['bathrooms'];
        }

        if (!empty($filters['agent_id'])) {
            $where[] = "p.agent_id = :agent_id";
            $params['agent_id'] = $filters['agent_id'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT COUNT(*) FROM properties p WHERE {$whereClause}";

        return $this->db->query($sql, $params)->fetchColumn();
    }

    /**
     * Create new property
     */
    public function create($data) {
        $this->errors = [];

        // Validate required fields
        $required = ['title', 'description', 'price', 'address', 'city', 'state', 'agent_id'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
            }
        }

        if (!empty($this->errors)) {
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
        // Get property images
        $images = $this->getImages($id);
        
        // Delete image files
        $upload = new Upload();
        foreach ($images as $image) {
            $upload->deleteFile(UPLOAD_PATH . 'properties/' . $image['image_path']);
            if ($image['thumbnail_path']) {
                $upload->deleteFile(UPLOAD_PATH . 'thumbnails/' . $image['thumbnail_path']);
            }
        }

        // Delete property (cascade will delete images from database)
        $this->db->delete('properties', 'id = :id', ['id' => $id]);

        return true;
    }

    /**
     * Add property image
     */
    public function addImage($propertyId, $imageData, $isPrimary = false) {
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
    public function deleteImage($imageId) {
        $image = $this->db->selectOne('property_images', '*', 'id = :id', ['id' => $imageId]);
        
        if ($image) {
            $upload = new Upload();
            $upload->deleteFile(UPLOAD_PATH . 'properties/' . $image['image_path']);
            if ($image['thumbnail_path']) {
                $upload->deleteFile(UPLOAD_PATH . 'thumbnails/' . $image['thumbnail_path']);
            }
            
            $this->db->delete('property_images', 'id = :id', ['id' => $imageId]);
        }

        return true;
    }

    /**
     * Set primary image
     */
    public function setPrimaryImage($propertyId, $imageId) {
        // Unset all primary images
        $this->db->update('property_images', ['is_primary' => 0], 'property_id = :property_id', ['property_id' => $propertyId]);
        
        // Set new primary
        $this->db->update('property_images', ['is_primary' => 1], 'id = :id', ['id' => $imageId]);

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
                (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
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
        return $this->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
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
        return $this->db->query($sql, $params)->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get agent properties
     */
    public function getAgentProperties($agentId, $status = null) {
        $where = 'agent_id = :agent_id';
        $params = ['agent_id' => $agentId];

        if ($status) {
            $where .= ' AND property_status = :status';
            $params['status'] = $status;
        }

        return $this->db->select('properties', '*', $where, $params, 'created_at DESC');
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