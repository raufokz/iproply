<?php
/**
 * Map public GET parameters → Property listing filter array (canonical keys).
 */

if (!function_exists('listing_filters_from_request')) {

    /** @param array<string, mixed> $get */
    /** @return array<string, mixed> */
    function listing_filters_from_request(array $get): array {
        $filters = [];

        $kw = trim((string) ($get['keyword'] ?? $get['search'] ?? ''));
        if ($kw !== '') {
            $filters['keyword'] = $kw;
        }

        $city = trim((string) ($get['city'] ?? ''));
        if ($city !== '') {
            $filters['city'] = $city;
        }

        $stateClean = strtoupper(trim((string) ($get['state'] ?? '')));
        if ($stateClean !== '' && preg_match('/^[A-Z]{2}$/', $stateClean)) {
            $filters['state'] = $stateClean;
        }

        $lat = isset($get['lat']) ? filter_var($get['lat'], FILTER_VALIDATE_FLOAT) : false;
        $lng = isset($get['lng']) ? filter_var($get['lng'], FILTER_VALIDATE_FLOAT) : false;
        if ($lat !== false && $lng !== false && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
            $filters['geo_lat'] = $lat;
            $filters['geo_lng'] = $lng;
            $rmiles = isset($get['radius_mi']) ? filter_var($get['radius_mi'], FILTER_VALIDATE_FLOAT) : false;
            if ($rmiles !== false) {
                $filters['geo_radius_mi'] = max(5.0, min(500.0, $rmiles));
            }
        }

        if (!empty($get['type']) && ctype_digit((string) $get['type'])) {
            $filters['property_type'] = (int) $get['type'];
        }

        if (!empty($get['status'])) {
            $st = strtolower((string) $get['status']);
            if ($st === 'rent' || $st === 'sale') {
                $filters['status_type'] = $st;
            }
        }

        foreach (['min_price', 'max_price'] as $priceKey) {
            if (!array_key_exists($priceKey, $get)) {
                continue;
            }
            $v = trim((string) $get[$priceKey]);
            if ($v === '') {
                continue;
            }
            if (!is_numeric($v)) {
                continue;
            }
            $nk = $priceKey === 'min_price' ? 'min_price' : 'max_price';
            $filters[$nk] = (float) $v;
        }

        foreach (['bedrooms', 'bathrooms'] as $k) {
            if (!array_key_exists($k, $get)) {
                continue;
            }
            $raw = trim((string) $get[$k]);
            if ($raw === '') {
                continue;
            }
            if (!is_numeric($raw)) {
                continue;
            }

            $filters[$k] = $k === 'bedrooms'
                ? (int) $raw
                : (float) $raw;
        }

        if (!empty($get['featured'])) {
            $filters['featured_only'] = true;
        }

        if (!empty($get['agent'])) {
            if (ctype_digit((string) $get['agent'])) {
                $filters['agent_id'] = (int) $get['agent'];
            }
        }

        return $filters;
    }
}

if (!function_exists('listing_filters_to_query')) {

    /**
     * Build query string fragments for pagination / redirects (effective filters → GET keys).
     *
     * @param array<string, mixed> $filters
     * @return array<string, scalar>
     */
    function listing_filters_to_query(array $filters): array {
        $q = [];

        if (!empty($filters['keyword'])) {
            $q['keyword'] = (string) $filters['keyword'];
        }

        if (!empty($filters['city'])) {
            $q['city'] = (string) $filters['city'];
        }

        if (!empty($filters['state'])) {
            $q['state'] = (string) $filters['state'];
        }

        if (!empty($filters['geo_lat']) && !empty($filters['geo_lng'])) {
            $q['lat'] = $filters['geo_lat'];
            $q['lng'] = $filters['geo_lng'];
            if (!empty($filters['geo_radius_mi'])) {
                $q['radius_mi'] = $filters['geo_radius_mi'];
            }
        }

        if (!empty($filters['property_type'])) {
            $q['type'] = $filters['property_type'];
        }

        if (!empty($filters['status_type'])) {
            $q['status'] = $filters['status_type'];
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '' && $filters['min_price'] !== null) {
            $q['min_price'] = $filters['min_price'];
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '' && $filters['max_price'] !== null) {
            $q['max_price'] = $filters['max_price'];
        }

        if (isset($filters['bedrooms']) && $filters['bedrooms'] !== '' && $filters['bedrooms'] !== null) {
            $q['bedrooms'] = $filters['bedrooms'];
        }

        if (isset($filters['bathrooms']) && $filters['bathrooms'] !== '' && $filters['bathrooms'] !== null) {
            $q['bathrooms'] = $filters['bathrooms'];
        }

        if (!empty($filters['featured_only'])) {
            $q['featured'] = 1;
        }

        if (!empty($filters['agent_id'])) {
            $q['agent'] = $filters['agent_id'];
        }

        return $q;
    }
}
