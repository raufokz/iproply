<?php
/**
 * Page Model Class
 * CMS-style content pages with publish status and footer navigation metadata.
 */
class Page {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getById($id) {
        return $this->db->selectOne('pages', '*', 'id = :id', ['id' => (int) $id]);
    }

    public function getBySlug($slug) {
        return $this->db->selectOne('pages', '*', 'slug = :slug', ['slug' => $slug]);
    }

    public function getPublishedBySlug($slug) {
        return $this->db->selectOne('pages', '*', 'slug = :slug AND status = :status', [
            'slug' => $slug,
            'status' => 'published',
        ]);
    }

    public function getAllForAdmin($status = 'all', $search = '') {
        $where = [];
        $params = [];

        if ($status !== 'all') {
            $where[] = 'status = :status';
            $params['status'] = $status === 'published' ? 'published' : 'draft';
        }

        if ($search !== '') {
            $where[] = '(title LIKE :search OR slug LIKE :search OR content LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $whereSql = !empty($where) ? implode(' AND ', $where) : '';
        return $this->db->select('pages', '*', $whereSql, $params, 'updated_at DESC, created_at DESC');
    }

    public function save($data, $adminId, $id = null) {
        $title = trim($data['title'] ?? '');
        $rawSlug = trim($data['slug'] ?? '');
        $content = trim($data['content'] ?? '');

        $status = ($data['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $showInFooter = !empty($data['show_in_footer']) ? 1 : 0;
        $footerSection = trim($data['footer_section'] ?? '');
        $footerOrder = isset($data['footer_order']) ? (int) $data['footer_order'] : 0;

        $metaTitle = trim($data['meta_title'] ?? '');
        $metaDescription = trim($data['meta_description'] ?? '');
        $metaKeywords = trim($data['meta_keywords'] ?? '');

        if ($title === '' || $content === '') {
            return false;
        }

        $slugSource = $rawSlug !== '' ? $rawSlug : $title;
        $slug = $this->generateUniqueSlug($slugSource, $id);

        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;

        $payload = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'status' => $status,
            'published_at' => $publishedAt,
            'meta_title' => $metaTitle !== '' ? $metaTitle : null,
            'meta_description' => $metaDescription !== '' ? $metaDescription : null,
            'meta_keywords' => $metaKeywords !== '' ? $metaKeywords : null,
            'show_in_footer' => $showInFooter,
            'footer_section' => $footerSection !== '' ? $footerSection : null,
            'footer_order' => $footerOrder,
            'updated_by' => (int) $adminId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            $this->db->update('pages', $payload, 'id = :id', ['id' => (int) $id]);
            return (int) $id;
        }

        $payload['created_by'] = (int) $adminId;
        $payload['created_at'] = date('Y-m-d H:i:s');

        return (int) $this->db->insert('pages', $payload);
    }

    public function delete($id) {
        return $this->db->delete('pages', 'id = :id', ['id' => (int) $id]);
    }

    public function getFooterPages($section) {
        return $this->db->select(
            'pages',
            '*',
            'status = :status AND show_in_footer = 1 AND footer_section = :section',
            ['status' => 'published', 'section' => $section],
            'footer_order ASC, title ASC'
        );
    }

    public static function renderContent($raw) {
        $raw = (string) ($raw ?? '');
        $lines = preg_split('/\R/u', $raw);

        $html = [];
        $inList = false;

        $closeList = function() use (&$html, &$inList) {
            if ($inList) {
                $html[] = '</ul>';
                $inList = false;
            }
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                $closeList();
                continue;
            }

            if (preg_match('/^(H1|H2|H3):\s*(.+)$/i', $trimmed, $matches)) {
                $closeList();
                $level = strtoupper($matches[1]);
                $text = sanitize($matches[2]);
                $tag = $level === 'H1' ? 'h2' : ($level === 'H2' ? 'h3' : 'h4');
                $html[] = "<{$tag} class=\"cms-heading\">{$text}</{$tag}>";
                continue;
            }

            if (preg_match('/^[-*]\s+(.+)$/', $trimmed, $matches)) {
                if (!$inList) {
                    $html[] = '<ul class="cms-list">';
                    $inList = true;
                }
                $html[] = '<li>' . sanitize($matches[1]) . '</li>';
                continue;
            }

            $closeList();
            $html[] = '<p>' . sanitize($trimmed) . '</p>';
        }

        $closeList();

        return implode("\n", $html);
    }

    private function generateUniqueSlug($text, $excludeId = null) {
        $baseSlug = generate_slug($text);
        if ($baseSlug === '') {
            $baseSlug = 'page';
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

            if (!$this->db->exists('pages', $where, $params)) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $i;
            $i++;
        }
    }
}

