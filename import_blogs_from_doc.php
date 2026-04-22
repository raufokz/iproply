<?php
/**
 * Import blogs from exported Google Doc text.
 * Usage: php import_blogs_from_doc.php
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Blog.php';

$docPath = __DIR__ . '/doc_blogs.txt';
if (!file_exists($docPath)) {
    exit("doc_blogs.txt not found.\n");
}

$db = Database::getInstance();
$blogModel = new Blog();

// Ensure blogs table exists for existing installations.
$migrationPath = __DIR__ . '/database-blog-migration.sql';
if (file_exists($migrationPath)) {
    $db->exec(file_get_contents($migrationPath));
}

$raw = file_get_contents($docPath);
$lines = preg_split("/\r\n|\n|\r/", $raw);
$totalLines = count($lines);

// Start lines identified from the shared document structure.
$starts = [1, 181, 511, 676, 905, 1108, 1544, 1819, 2057, 2224, 2376, 2539];

$segments = [];
for ($i = 0; $i < count($starts); $i++) {
    $start = $starts[$i];
    $end = ($i < count($starts) - 1) ? ($starts[$i + 1] - 1) : $totalLines;
    $segments[] = [$start, $end];
}

function normalize_title($line) {
    $line = trim($line);
    $line = preg_replace('/^H1:\s*/i', '', $line);
    return trim($line);
}

function clean_chunk_content($chunkLines, $titleCandidates) {
    $cleaned = [];
    foreach ($chunkLines as $line) {
        $trim = trim($line);
        if ($trim === '________________') {
            $cleaned[] = "";
            continue;
        }
        if (in_array($trim, $titleCandidates, true)) {
            continue;
        }
        $cleaned[] = rtrim($line);
    }
    // Collapse excessive blank lines
    $content = implode("\n", $cleaned);
    $content = preg_replace("/\n{3,}/", "\n\n", $content);
    return trim($content);
}

function build_excerpt($content) {
    $plain = preg_replace('/\s+/', ' ', strip_tags($content));
    $plain = trim($plain);
    if (strlen($plain) <= 200) {
        return $plain;
    }
    return substr($plain, 0, 200) . '...';
}

$adminId = current_user_id();
if (!$adminId || !is_admin()) {
    // fallback to first admin when running from CLI without session
    $firstAdmin = $db->selectOne('admins', 'id', '', []);
    $adminId = $firstAdmin['id'] ?? 1;
}

$created = 0;
$updated = 0;

foreach ($segments as $segment) {
    [$start, $end] = $segment;
    $chunkLines = array_slice($lines, $start - 1, $end - $start + 1);
    if (empty($chunkLines)) {
        continue;
    }

    $first = normalize_title($chunkLines[0] ?? '');
    $second = normalize_title($chunkLines[1] ?? '');
    $title = $first;

    if ($second !== '' && !preg_match('/^Meta Description/i', $second)) {
        // Prefer fuller second line title when available.
        if (strlen($second) > strlen($first) || preg_match('/guide|2026|usa|california|houston/i', $second)) {
            $title = $second;
        }
    }

    if ($title === '') {
        continue;
    }

    $titleCandidates = array_filter([$first, $second]);
    $content = clean_chunk_content($chunkLines, $titleCandidates);
    if ($content === '') {
        continue;
    }

    $existing = $db->selectOne('blogs', 'id', 'title = :title', ['title' => $title]);
    $payload = [
        'title' => $title,
        'excerpt' => build_excerpt($content),
        'content' => $content,
        'status' => 'published'
    ];

    if ($existing) {
        $blogModel->save($payload, $adminId, (int) $existing['id']);
        $updated++;
    } else {
        $blogModel->save($payload, $adminId, null);
        $created++;
    }
}

echo "Import complete. Created: {$created}, Updated: {$updated}\n";
