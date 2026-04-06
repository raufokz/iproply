<?php
/**
 * Inquiry Model Class
 * Handles all inquiry-related database operations
 */

class Inquiry {
    private $db;
    private $errors = [];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get all inquiries
     */
    public function getAll($filters = [], $page = 1, $perPage = ADMIN_ITEMS_PER_PAGE) {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['agent_id'])) {
            $where[] = "i.agent_id = :agent_id";
            $params['agent_id'] = $filters['agent_id'];
        }

        if (!empty($filters['property_id'])) {
            $where[] = "i.property_id = :property_id";
            $params['property_id'] = $filters['property_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = "i.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(i.name LIKE :search OR i.email LIKE :search OR i.message LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT i.*, 
                p.title as property_title,
                p.slug as property_slug,
                p.price as property_price,
                p.status as property_status,
                CONCAT(a.first_name, ' ', a.last_name) as agent_name,
                a.email as agent_email
                FROM inquiries i
                LEFT JOIN properties p ON i.property_id = p.id
                LEFT JOIN agents a ON i.agent_id = a.id
                WHERE {$whereClause}
                ORDER BY i.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get inquiry by ID
     */
    public function getById($id) {
        $sql = "SELECT i.*, 
                p.title as property_title,
                p.slug as property_slug,
                p.price as property_price,
                p.status as property_status,
                CONCAT(a.first_name, ' ', a.last_name) as agent_name,
                a.email as agent_email,
                a.phone as agent_phone
                FROM inquiries i
                LEFT JOIN properties p ON i.property_id = p.id
                LEFT JOIN agents a ON i.agent_id = a.id
                WHERE i.id = :id";

        return $this->db->query($sql, ['id' => $id])->fetch();
    }

    /**
     * Get total count
     */
    public function getTotalCount($filters = []) {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['agent_id'])) {
            $where[] = "agent_id = :agent_id";
            $params['agent_id'] = $filters['agent_id'];
        }

        if (!empty($filters['property_id'])) {
            $where[] = "property_id = :property_id";
            $params['property_id'] = $filters['property_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = "status = :status";
            $params['status'] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);

        return $this->db->count('inquiries', $whereClause, $params);
    }

    /**
     * Create new inquiry
     */
    public function create($data) {
        $this->errors = [];

        // Validation
        if (empty($data['name'])) {
            $this->errors[] = "Name is required";
        }

        if (empty($data['email'])) {
            $this->errors[] = "Email is required";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Invalid email format";
        }

        if (empty($data['message'])) {
            $this->errors[] = "Message is required";
        }

        if (empty($data['property_id'])) {
            $this->errors[] = "Property is required";
        }

        if (!empty($this->errors)) {
            return false;
        }

        // Get property agent if not specified
        if (empty($data['agent_id'])) {
            $property = $this->db->selectOne('properties', 'agent_id', 'id = :id', ['id' => $data['property_id']]);
            if ($property) {
                $data['agent_id'] = $property['agent_id'];
            } else {
                $this->errors[] = "Property not found";
                return false;
            }
        }

        // Set default values
        $data['status'] = 'new';
        $data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $data['created_at'] = date('Y-m-d H:i:s');

        // Insert inquiry
        $inquiryId = $this->db->insert('inquiries', $data);

        return $inquiryId;
    }

    /**
     * Update inquiry status
     */
    public function updateStatus($id, $status, $respondedBy = null) {
        $data = ['status' => $status];

        if ($status === 'responded' && $respondedBy) {
            $data['responded_at'] = date('Y-m-d H:i:s');
            $data['responded_by'] = $respondedBy;
        }

        $this->db->update('inquiries', $data, 'id = :id', ['id' => $id]);

        return true;
    }

    /**
     * Mark as read
     */
    public function markAsRead($id) {
        $inquiry = $this->getById($id);
        
        if ($inquiry && $inquiry['status'] === 'new') {
            $this->db->update('inquiries', ['status' => 'read'], 'id = :id', ['id' => $id]);
        }

        return true;
    }

    /**
     * Add notes
     */
    public function addNotes($id, $notes) {
        $this->db->update('inquiries', ['notes' => $notes], 'id = :id', ['id' => $id]);
        return true;
    }

    /**
     * Delete inquiry
     */
    public function delete($id) {
        $this->db->delete('inquiries', 'id = :id', ['id' => $id]);
        return true;
    }

    /**
     * Get new inquiries count for agent
     */
    public function getNewCount($agentId = null) {
        $where = "status = 'new'";
        $params = [];

        if ($agentId) {
            $where .= " AND agent_id = :agent_id";
            $params['agent_id'] = $agentId;
        }

        return $this->db->count('inquiries', $where, $params);
    }

    /**
     * Get recent inquiries
     */
    public function getRecent($limit = 5, $agentId = null) {
        $where = '1=1';
        $params = [];

        if ($agentId) {
            $where .= " AND i.agent_id = :agent_id";
            $params['agent_id'] = $agentId;
        }

        $sql = "SELECT i.*, 
                p.title as property_title,
                p.slug as property_slug,
                CONCAT(a.first_name, ' ', a.last_name) as agent_name
                FROM inquiries i
                LEFT JOIN properties p ON i.property_id = p.id
                LEFT JOIN agents a ON i.agent_id = a.id
                WHERE {$where}
                ORDER BY i.created_at DESC
                LIMIT {$limit}";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get inquiry statistics
     */
    public function getStats($agentId = null) {
        $where = '1=1';
        $params = [];

        if ($agentId) {
            $where = 'agent_id = :agent_id';
            $params['agent_id'] = $agentId;
        }

        $sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'new' THEN 1 END) as new_count,
                COUNT(CASE WHEN status = 'read' THEN 1 END) as read_count,
                COUNT(CASE WHEN status = 'responded' THEN 1 END) as responded_count,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_count
                FROM inquiries
                WHERE {$where}";

        return $this->db->query($sql, $params)->fetch();
    }

    /**
     * Get errors
     */
    public function getErrors() {
        return $this->errors;
    }
}