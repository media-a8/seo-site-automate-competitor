<?php

declare(strict_types=1);

$root = __DIR__;

require $root . '/includes/helpers.php';

$site = load_json($root . '/config/site.json');
$slug = resolve_slug($_SERVER['REQUEST_URI'] ?? '/');

$isNotFound = false;

if ($slug === '' || !page_exists($root, $slug)) {
    $isNotFound = true;
    http_response_code(404);
    $slug = page_exists($root, 'notfound') ? 'notfound' : '';
}

if ($slug === '') {
    http_response_code(404);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="th"><head><meta charset="utf-8"><title>404</title></head><body><h1>404 Not Found</h1></body></html>';
    exit;
}

$pageFile = resolve_page_basename($root, $slug);
$metaFile = resolve_meta_basename($root, $slug);

$meta = $metaFile !== '' ? load_json($root . '/' . $metaFile) : [];
if ($meta === [] && $slug === 'notfound') {
    $meta = [
        'meta_title' => 'ไม่พบหน้า | ' . ($site['brand'] ?? ''),
        'meta_description' => 'ไม่พบหน้าที่คุณต้องการ',
        'social_tags' => [
            'og_title' => 'ไม่พบหน้า',
            'og_description' => 'ไม่พบหน้าที่คุณต้องการ',
            'og_image' => (string) ($site['logo_url'] ?? ''),
            'og_type' => 'website',
        ],
    ];
}

$navPath = $root . '/partials/nav.html';
$footerPath = $root . '/partials/footer.html';
$bodyPath = $pageFile !== '' ? ($root . '/' . $pageFile) : '';

$navHtml = is_file($navPath) ? (string) file_get_contents($navPath) : '';
$footerHtml = is_file($footerPath) ? (string) file_get_contents($footerPath) : '';
$bodyHtml = ($bodyPath !== '' && is_file($bodyPath))
    ? (string) file_get_contents($bodyPath)
    : '<p>เนื้อหาว่าง</p>';

if ($slug === 'articles') {
    $articles = list_articles($root);
    if ($articles === []) {
        $listHtml = '<div class="prose"><p>กำลังเตรียมบทความใหม่ๆ เร็วๆ นี้</p></div>';
    } else {
        $cards = '';
        foreach ($articles as $article) {
            $cards .= '<a class="card" href="/articles/' . e($article['slug']) . '">'
                . '<h3>' . e($article['title']) . '</h3>'
                . '<p>' . e($article['description']) . '</p>'
                . '</a>';
        }
        $listHtml = '<div class="feature-row">' . $cards . '</div>';
    }

    $marker = '<!--ARTICLE_LIST-->';
    if (strpos($bodyHtml, $marker) !== false) {
        $bodyHtml = str_replace($marker, $listHtml, $bodyHtml);
    } else {
        $bodyHtml .= $listHtml;
    }
}

header('Content-Type: text/html; charset=utf-8');
require $root . '/includes/layout.php';
