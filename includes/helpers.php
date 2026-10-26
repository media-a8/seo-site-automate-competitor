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
    // Only allow simple slugs (no nested paths)
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
 * Real pages/{slug}.html basename (case-insensitive).
 */
function resolve_page_basename(string $root, string $slug): string
{
    if ($slug === '') {
        return '';
    }
    return find_ci_file($root . '/pages', $slug . '.html');
}

/**
 * Real meta/{slug}.json basename (case-insensitive).
 */
function resolve_meta_basename(string $root, string $slug): string
{
    if ($slug === '') {
        return '';
    }
    return find_ci_file($root . '/meta', $slug . '.json');
}

/**
 * Whether a page body file exists.
 */
function page_exists(string $root, string $slug): bool
{
    return resolve_page_basename($root, $slug) !== '';
}