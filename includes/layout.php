<?php
/** @var array $site */
/** @var array $meta */
/** @var string $slug */
/** @var string $navHtml */
/** @var string $bodyHtml */
/** @var string $footerHtml */
/** @var bool $isNotFound */

$brand = (string) ($site['brand'] ?? '');
$lang = (string) ($site['site_lang'] ?? 'th');
$logoUrl = (string) ($site['logo_url'] ?? '');
$title = (string) ($meta['meta_title'] ?? $brand);
$description = (string) ($meta['meta_description'] ?? '');
$social = is_array($meta['social_tags'] ?? null) ? $meta['social_tags'] : [];
$ogTitle = (string) ($social['og_title'] ?? $title);
$ogDescription = (string) ($social['og_description'] ?? $description);
$ogImage = (string) ($social['og_image'] ?? ($site['logo_url'] ?? ''));
$ogType = (string) ($social['og_type'] ?? 'website');
$canonical = canonical_url($site, $slug === 'notfound' ? 'homepage' : $slug);

if (!function_exists('schema_plain_text')) {
    function schema_plain_text(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';
        return trim($text);
    }
}

if (!function_exists('schema_faq_items')) {
    function schema_faq_items(string $html): array
    {
        if ($html === '') {
            return [];
        }

        if (!preg_match_all('/<section\b[^>]*>.*?<\/section>/is', $html, $sections)) {
            return [];
        }

        $faqHtml = '';
        foreach ($sections[0] as $section) {
            if (preg_match('/class=[\'"][^\'"]*\bfaq\b/i', $section)) {
                $faqHtml = $section;
                break;
            }
        }

        if ($faqHtml === '') {
            foreach ($sections[0] as $section) {
                if (preg_match('/คำถามที่พบบ่อย|frequently asked|\bfaq\b/iu', $section)) {
                    $faqHtml = $section;
                    break;
                }
            }
        }

        if ($faqHtml === '') {
            return [];
        }

        if (!preg_match_all('/<h3\b[^>]*>(.*?)<\/h3>\s*<p\b[^>]*>(.*?)<\/p>/is', $faqHtml, $pairs, PREG_SET_ORDER)) {
            return [];
        }

        $items = [];
        foreach ($pairs as $pair) {
            $question = schema_plain_text($pair[1]);
            $answer = schema_plain_text($pair[2]);
            if ($question === '' || $answer === '') {
                continue;
            }
            $items[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        return $items;
    }
}

$schemaGraph = [];

if (!$isNotFound) {
    $publisher = [
        '@type' => 'Organization',
        'name' => $brand,
    ];
    if ($ogImage !== '') {
        $publisher['logo'] = [
            '@type' => 'ImageObject',
            'url' => $ogImage,
        ];
    }

    $article = [
        '@type' => 'Article',
        'headline' => $title,
        'description' => $description,
        'inLanguage' => $lang,
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $canonical,
        ],
        'author' => [
            '@type' => 'Organization',
            'name' => $brand,
        ],
        'publisher' => $publisher,
    ];
    if ($ogImage !== '') {
        $article['image'] = $ogImage;
    }
    $schemaGraph[] = $article;

    if ($slug === 'homepage') {
        $faqItems = schema_faq_items((string) $bodyHtml);
        if ($faqItems !== []) {
            $schemaGraph[] = [
                '@type' => 'FAQPage',
                'mainEntity' => $faqItems,
            ];
        }
    }
}

$schemaJson = $schemaGraph === []
    ? ''
    : json_encode(
        [
            '@context' => 'https://schema.org',
            '@graph' => $schemaGraph,
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP
    );
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
<?php if ($description !== ''): ?>
    <meta name="description" content="<?= e($description) ?>">
<?php endif; ?>
<?php if (!$isNotFound && $canonical !== ''): ?>
    <link rel="canonical" href="<?= e($canonical) ?>">
<?php endif; ?>
<?php if ($isNotFound): ?>
    <meta name="robots" content="noindex, follow">
<?php endif; ?>
    <meta property="og:title" content="<?= e($ogTitle) ?>">
    <meta property="og:description" content="<?= e($ogDescription) ?>">
    <meta property="og:type" content="<?= e($ogType) ?>">
<?php if ($ogImage !== ''): ?>
    <meta property="og:image" content="<?= e($ogImage) ?>">
<?php endif; ?>
<?php if (!$isNotFound && $canonical !== ''): ?>
    <meta property="og:url" content="<?= e($canonical) ?>">
<?php endif; ?>
<?php if ($logoUrl !== ''): ?>
    <link rel="icon" href="<?= e($logoUrl) ?>">
    <link rel="apple-touch-icon" href="<?= e($logoUrl) ?>">
<?php endif; ?>
    <link rel="stylesheet" href="/assets/css/theme.css">
<?php if ($schemaJson !== ''): ?>
    <script type="application/ld+json"><?= $schemaJson ?></script>
<?php endif; ?>
</head>
<body class="page-<?= e(str_replace('/', '-', $slug)) ?>">
    <a class="skip-link" href="#main">ข้ามไปยังเนื้อหา</a>
    <?= $navHtml ?>
    <main id="main">
        <?= $bodyHtml ?>
    </main>
    <?= $footerHtml ?>
</body>
</html>