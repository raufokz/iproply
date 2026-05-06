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

    public function getPublished($page = 1, $perPage = 9, $search = '') {
        $page = max(1, (int) $page);
        $perPage = max(1, (int) $perPage);
        $offset = ($page - 1) * $perPage;

        $params = ['status' => 'published'];
        $where = 'b.status = :status';
        $search = trim((string) $search);
        if ($search !== '') {
            $pat = '%' . $search . '%';
            $where .= ' AND (b.title LIKE :blog_q1 OR b.excerpt LIKE :blog_q2 OR b.content LIKE :blog_q3)';
            $params['blog_q1'] = $pat;
            $params['blog_q2'] = $pat;
            $params['blog_q3'] = $pat;
        }

        $sql = "SELECT b.*, CONCAT(a.first_name, ' ', a.last_name) AS author_name
                FROM blogs b
                LEFT JOIN admins a ON b.created_by = a.id
                WHERE {$where}
                ORDER BY COALESCE(b.published_at, b.created_at) DESC
                LIMIT {$perPage} OFFSET {$offset}";

        return $this->db->query($sql, $params)->fetchAll();
    }

    public function countPublished($search = '') {
        $params = [];
        $where = "status = 'published'";
        $search = trim((string) $search);
        if ($search !== '') {
            $pat = '%' . $search . '%';
            $where .= ' AND (title LIKE :blog_c1 OR excerpt LIKE :blog_c2 OR content LIKE :blog_c3)';
            $params['blog_c1'] = $pat;
            $params['blog_c2'] = $pat;
            $params['blog_c3'] = $pat;
        }

        return (int) $this->db->query("SELECT COUNT(*) FROM blogs WHERE {$where}", $params)->fetchColumn();
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
            $pat = '%' . $search . '%';
            $where[] = '(b.title LIKE :blog_ad1 OR b.content LIKE :blog_ad2 OR b.excerpt LIKE :blog_ad3)';
            $params['blog_ad1'] = $pat;
            $params['blog_ad2'] = $pat;
            $params['blog_ad3'] = $pat;
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
