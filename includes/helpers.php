<?php

/**
 * Load and decode a JSON file. Returns [] on failure.
 */
function load_json(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $raw = file_get_contents($path);
    if ($raw === false || $raw === '') {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Escape for HTML text/attributes.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Find a file in a directory by name, case-insensitive.
 * Returns the real basename, or '' if missing.
 */
function find_ci_file(string $dir, string $desiredName): string
{
    if ($desiredName === '' || !is_dir($dir)) {
        return '';
    }

    $desiredLower = strtolower($desiredName);
    foreach (scandir($dir) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        if (strtolower($entry) === $desiredLower && is_file($dir . '/' . $entry)) {
            return $entry;
        }
    }

    return '';
}

/**
 * Resolve request path to a page slug.
 * "" or "homepage" → homepage
 */
function resolve_slug(string $requestUri): string
{
    $path = parse_url($requestUri, PHP_URL_PATH);
    $path = is_string($path) ? $path : '/';
    $path = '/' . trim($path, '/');

    if ($path === '/') {
        return 'homepage';
    }

    $slug = ltrim($path, '/');
    // Single slug, or one article under /articles/{slug}
    if (preg_match('#^articles/[a-z0-9\-]+$#i', $slug)) {
        return strtolower($slug);
    }
    if (!preg_match('/^[a-z0-9\-]+$/i', $slug)) {
        return '';
    }

    if ($slug === 'homepage') {
        return 'homepage';
    }

    return strtolower($slug);
}

/**
 * Absolute canonical URL for a slug.
 */
function canonical_url(array $site, string $slug): string
{
    $domain = rtrim((string) ($site['domain'] ?? ''), '/');
    if ($domain === '') {
        $domain = '';
    }

    if ($slug === 'homepage') {
        return $domain === '' ? '/' : $domain . '/';
    }

    return ($domain === '' ? '' : $domain) . '/' . $slug;
}

/**
 * Public href for a nav path (homepage → /).
 */
function nav_href(string $path): string
{
    if ($path === 'homepage' || $path === '') {
        return '/';
    }
    return '/' . ltrim($path, '/');
}

/**
 * Path of the HTML body relative to $root, or ''.
 * pages/{slug}.html, or posts/{slug}.html when the slug is articles/{slug}.
 */
function resolve_page_basename(string $root, string $slug): string
{
    if ($slug === '') {
        return '';
    }

    if (strpos($slug, 'articles/') === 0) {
        $name = substr($slug, strlen('articles/'));
        $file = find_ci_file($root . '/posts', $name . '.html');
        return $file === '' ? '' : 'posts/' . $file;
    }

    $file = find_ci_file($root . '/pages', $slug . '.html');
    return $file === '' ? '' : 'pages/' . $file;
}

/**
 * Path of the meta JSON relative to $root, or ''.
 * meta/{slug}.json, or posts/{slug}.json when the slug is articles/{slug}.
 */
function resolve_meta_basename(string $root, string $slug): string
{
    if ($slug === '') {
        return '';
    }

    if (strpos($slug, 'articles/') === 0) {
        $name = substr($slug, strlen('articles/'));
        $file = find_ci_file($root . '/posts', $name . '.json');
        return $file === '' ? '' : 'posts/' . $file;
    }

    $file = find_ci_file($root . '/meta', $slug . '.json');
    return $file === '' ? '' : 'meta/' . $file;
}

/**
 * Whether a page body file exists.
 * An article slug also needs its matching JSON.
 */
function page_exists(string $root, string $slug): bool
{
    if (strpos($slug, 'articles/') === 0) {
        return resolve_page_basename($root, $slug) !== ''
            && resolve_meta_basename($root, $slug) !== '';
    }

    return resolve_page_basename($root, $slug) !== '';
}

/**
 * Published articles: both .html and .json. Newest date first, then slug.
 *
 * @return list<array{slug:string,title:string,description:string,date:string}>
 */
function list_articles(string $root): array
{
    $items = [];
    foreach (glob($root . '/posts/*.json') ?: [] as $jsonPath) {
        $base = basename($jsonPath, '.json');
        if (!preg_match('/^[a-z0-9\-]+$/i', $base)) {
            continue;
        }
        if (find_ci_file($root . '/posts', $base . '.html') === '') {
            continue;
        }

        $meta = load_json($jsonPath);
        $items[] = [
            'slug' => strtolower($base),
            'title' => (string) ($meta['meta_title'] ?? ''),
            'description' => (string) ($meta['meta_description'] ?? ''),
            'date' => (string) ($meta['date'] ?? ''),
        ];
    }

    usort($items, static function (array $a, array $b): int {
        $byDate = strcmp($b['date'], $a['date']);
        if ($byDate !== 0) {
            return $byDate;
        }
        return strcmp($a['slug'], $b['slug']);
    });

    return $items;
}

/**
 * Append an Articles link before the first nav list closes.
 * Skips when the link is already present, or the list markup is missing.
 */
function inject_articles_nav(string $navHtml): string
{
    if (strpos($navHtml, 'href="/articles"') !== false) {
        return $navHtml;
    }

    $open = strpos($navHtml, '<ul class="nav__list">');
    if ($open === false) {
        return $navHtml;
    }

    $close = strpos($navHtml, '</ul>', $open);
    if ($close === false) {
        return $navHtml;
    }

    $item = '<li><a class="nav__link" href="/articles">บทความ</a></li>';
    return substr($navHtml, 0, $close) . $item . substr($navHtml, $close);
}
