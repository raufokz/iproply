<?php
/**
 * Blog Model Class
 * Handles blog post CRUD and frontend listing operations.
 */
class Blog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getPublished($page = 1, $perPage = 9) {
        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT b.*, CONCAT(a.first_name, ' ', a.last_name) AS author_name
                FROM blogs b
                LEFT JOIN admins a ON b.created_by = a.id
                WHERE b.status = 'published'
                ORDER BY COALESCE(b.published_at, b.created_at) DESC
                LIMIT {$perPage} OFFSET {$offset}";

        return $this->db->query($sql)->fetchAll();
    }

    public function countPublished() {
        return (int) $this->db->query("SELECT COUNT(*) FROM blogs WHERE status = 'published'")->fetchColumn();
    }

    public function getBySlug($slug) {
        $sql = "SELECT b.*, CONCAT(a.first_name, ' ', a.last_name) AS author_name
                FROM blogs b
                LEFT JOIN admins a ON b.created_by = a.id
                WHERE b.slug = :slug AND b.status = 'published'
                LIMIT 1";

        return $this->db->query($sql, ['slug' => $slug])->fetch();
    }

    public function getById($id) {
        return $this->db->selectOne('blogs', '*', 'id = :id', ['id' => (int) $id]);
    }

    public function getAllForAdmin($status = 'all', $search = '') {
        $where = [];
        $params = [];

        if ($status !== 'all') {
            $where[] = 'b.status = :status';
            $params['status'] = $status;
        }

        if ($search !== '') {
            $where[] = '(b.title LIKE :search OR b.content LIKE :search OR b.excerpt LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT b.*, CONCAT(a.first_name, ' ', a.last_name) AS author_name
                FROM blogs b
                LEFT JOIN admins a ON b.created_by = a.id
                {$whereSql}
                ORDER BY b.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    public function save($data, $adminId, $id = null) {
        $title = trim($data['title'] ?? '');
        $excerpt = trim($data['excerpt'] ?? '');
        $content = trim($data['content'] ?? '');
        $status = ($data['status'] ?? 'draft') === 'published' ? 'published' : 'draft';

        if ($title === '' || $content === '') {
            return false;
        }

        $slug = $this->generateUniqueSlug($title, $id);
        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;

        $payload = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'status' => $status,
            'published_at' => $publishedAt,
            'updated_by' => (int) $adminId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($id) {
            $this->db->update('blogs', $payload, 'id = :id', ['id' => (int) $id]);
            return (int) $id;
        }

        $payload['created_by'] = (int) $adminId;
        $payload['created_at'] = date('Y-m-d H:i:s');

        return (int) $this->db->insert('blogs', $payload);
    }

    public function delete($id) {
        return $this->db->delete('blogs', 'id = :id', ['id' => (int) $id]);
    }

    private function generateUniqueSlug($title, $excludeId = null) {
        $baseSlug = generate_slug($title);
        if ($baseSlug === '') {
            $baseSlug = 'blog-post';
        }

        $slug = $baseSlug;
        $i = 1;

        while (true) {
            $params = ['slug' => $slug];
            $where = 'slug = :slug';

            if ($excludeId) {
                $where .= ' AND id != :id';
                $params['id'] = (int) $excludeId;
            }

            if (!$this->db->exists('blogs', $where, $params)) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $i;
            $i++;
        }
    }
}
