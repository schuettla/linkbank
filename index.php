<?php
// --- CONFIGURATION & DATA LOADING ---

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('DATA_FILE', 'data.json');

// --- HELPER FUNCTIONS ---

/**
 * Loads and decodes the JSON data.
 */
function load_data() {
    if (!file_exists(DATA_FILE)) {
        die('Error: ' . DATA_FILE . ' not found.');
    }
    $json_string = file_get_contents(DATA_FILE);
    if ($json_string === false) {
        die('Error: Could not read ' . DATA_FILE . '.');
    }
    $data = json_decode($json_string);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Error: Invalid JSON in ' . DATA_FILE . '. Details: ' . json_last_error_msg());
    }
    return $data;
}

/**
 * Retrieves a nested property from an object using a dot-notation path.
 */
function get_config($data, $path, $default = null) {
    $keys = explode('.', $path);
    $value = $data;
    foreach ($keys as $key) {
        if (is_object($value) && isset($value->{$key})) {
            $value = $value->{$key};
        } elseif (is_array($value) && isset($value[$key])) {
            $value = $value[$key];
        } else {
            return $default;
        }
    }
    return $value;
}

/**
 * Returns an SVG icon for a given social media platform.
 * Icons are styled to inherit color via CSS 'currentColor'.
 * @param string $name The name of the social media platform (e.g., 'x').
 * @return string The SVG code or an empty string.
 */
function get_social_icon($name) {
    // SVGs are embedded for self-containment.
    // Stroke-based icons use `stroke="currentColor"`.
    // Fill-based icons use `fill="currentColor"` and have stroke/fill="none" removed.
    $icons = [
        'github' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>',
        'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>',
        'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>',
        
        // --- RENAMED & UPDATED ICON ---
        'x' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/></svg>',

        // --- NEW ICONS ---
        'email' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
        'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
        'tiktok' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20 9.2a6.3 6.3 0 0 1-3.4-1.1V15a5 5 0 1 1-5-5h2a3 3 0 1 0 3-3V5.4A6.3 6.3 0 0 1 12.5 4 6.5 6.5 0 1 1 20 9.2Z"/></svg>',
        'youtube' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M21.58 7.19c-.23-.86-.9-1.52-1.76-1.75C18.26 5 12 5 12 5s-6.26 0-7.82.44C3.32 5.67 2.66 6.33 2.43 7.19 2 8.74 2 12 2 12s0 3.26.43 4.81c.23.86.9 1.52 1.76 1.75C5.74 19 12 19 12 19s6.26 0 7.82-.44c.86-.23 1.52-.9 1.76-1.75C22 15.26 22 12 22 12s0-3.26-.42-4.81zM9.54 15.57V8.43L15.65 12l-6.11 3.57z"/></svg>',
        'pinterest' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.33.15c-6.23 0-9.4 4.32-9.4 8.73 0 3.82 2.22 7.2 5.53 8.35.1.07.24.03.28-.1l.36-1.53c.06-.27.02-.57-.12-.79a8.3 8.3 0 0 1-.9-3.2c0-2.3 1.67-4.7 4.1-4.7 2.2 0 3.5 1.6 3.5 3.82 0 2.6-1.2 5.3-2.9 5.3-.9 0-1.8-1-1.5-2.2l.6-2.5c.3-.9.9-1.9 2.2-1.9.9 0 2.2.8 2.2 2.8 0 3-2.3 5.4-5.3 5.4A6.3 6.3 0 0 1 6.6 13c0-1.2.5-2.2.8-2.7l1.9-8.2C9.5 1 10.4.15 12.33.15Z"/></svg>',
        'threads' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9.22A3.5 3.5 0 1 0 8.5 5.72a3.5 3.5 0 0 0 3.5 3.5Z"/><path d="M12 14.78a3.5 3.5 0 1 0 3.5 3.5 3.5 3.5 0 0 0-3.5-3.5Z"/><path d="M15.5 5.72c0-1.83-2-3.22-4.5-3.22s-4.5 1.39-4.5 3.22c0 .94.57 1.83 1.5 2.5M8.5 18.28c0 1.83 2 3.22 4.5 3.22s4.5-1.39 4.5-3.22c0-.94-.57-1.83-1.5-2.5"/></svg>',
    ];
    return $icons[strtolower($name)] ?? '';
}

// Load the data from the JSON file.
$data = load_data();


// --- SEO & PAGE VARIABLE SETUP ---

// Get base info and sanitize it immediately.
$title = htmlspecialchars(get_config($data, 'profile_info.title', 'My Profile'));
$subtitle = htmlspecialchars(get_config($data, 'profile_info.subtitle', ''));
$profile_image_url = get_config($data, 'profile_info.profile_image');

// 1. Create the full page title as requested: "Title | Subtitle"
$page_title_string = $title;
if (!empty($subtitle)) {
    $page_title_string .= ' | ' . $subtitle;
}

// 2. Create a meta description. Use the subtitle, with a fallback.
$meta_description = !empty($subtitle) ? $subtitle : "A collection of important links for " . $title . ".";

// 3. Dynamically determine the full canonical URL for SEO tags.
$canonical_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];


// --- DYNAMIC STYLE & VARIABLE SETUP ---

$font_family = get_config($data, 'layout_config.font.family_name', 'sans-serif');
$font_google_url = get_config($data, 'layout_config.font.google_font_url');
$font_size = get_config($data, 'layout_config.font.size', '16px');

// Smarter background handling
$bg_value = get_config($data, 'layout_config.colors.background', '#FFFFFF');
$background_style_string = '';
if (str_contains($bg_value, 'url(') || str_contains($bg_value, 'gradient')) {
    $background_style_string = "background: " . $bg_value . ";";
} else {
    $background_style_string = "background-color: " . htmlspecialchars($bg_value) . ";";
}

$text_color = get_config($data, 'layout_config.colors.text', '#111827');
$accent_color = get_config($data, 'layout_config.colors.accent', '#000000');
$button_color = get_config($data, 'layout_config.colors.button', '#F3F4F6');
$button_text_color = get_config($data, 'layout_config.colors.button_text', '#111827');
$button_style = get_config($data, 'layout_config.button.style', 'filled');
$button_radius = get_config($data, 'layout_config.button.border_radius', '8px');
$button_border_width = get_config($data, 'layout_config.button.border_width', '2px');
$button_shadow = get_config($data, 'layout_config.button.shadow', 'none');
$hover_transform = get_config($data, 'layout_config.hover_effects.button_transform', 'scale(1.02)');
$hover_button_bg = get_config($data, 'layout_config.hover_effects.button_background', $button_color);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- =================================================================
    ==                        SEO & META TAGS                           ==
    ================================================================== -->
    
    <!-- UPDATED: Dynamic Page Title -->
    <title><?= $page_title_string ?></title>

    <!-- Basic SEO Meta Tags -->
    <meta name="description" content="<?= $meta_description ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical_url) ?>" />

    <!-- Open Graph / Facebook Meta Tags -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($canonical_url) ?>">
    <meta property="og:title" content="<?= $page_title_string ?>">
    <meta property="og:description" content="<?= $meta_description ?>">
    <?php if ($profile_image_url): ?>
    <meta property="og:image" content="<?= htmlspecialchars($profile_image_url) ?>">
    <?php endif; ?>

    <!-- X (Twitter) Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= htmlspecialchars($canonical_url) ?>">
    <meta name="twitter:title" content="<?= $page_title_string ?>">
    <meta name="twitter:description" content="<?= $meta_description ?>">
    <?php if ($profile_image_url): ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($profile_image_url) ?>">
    <?php endif; ?>

    <!-- =================================================================
    ==                      STYLES & FONTS                              ==
    ================================================================== -->
    
    <!-- Dynamically import Google Font -->
    <?php if ($font_google_url): ?>
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="<?= htmlspecialchars($font_google_url) ?>" rel="stylesheet">
    <?php endif; ?>

    <!-- Dynamically generated CSS -->
    <style>
        :root {
            --font-family: '<?= htmlspecialchars($font_family) ?>', sans-serif;
            --font-size: <?= htmlspecialchars($font_size) ?>;
            --text-color: <?= htmlspecialchars($text_color) ?>;
            --accent-color: <?= htmlspecialchars($accent_color) ?>;
            --button-bg-color: <?= htmlspecialchars($button_color) ?>;
            --button-text-color: <?= htmlspecialchars($button_text_color) ?>;
            --button-border-radius: <?= htmlspecialchars($button_radius) ?>;
            --button-shadow: <?= $button_shadow ?>;
            --button-hover-bg: <?= htmlspecialchars($hover_button_bg) ?>;
            --button-hover-transform: <?= $hover_transform ?>;
            --button-border-width: <?= htmlspecialchars($button_border_width) ?>;
        }
        
        body {
            font-family: var(--font-family);
            font-size: var(--font-size);
            color: var(--text-color);
            <?= $background_style_string ?>
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 40px 20px;
        }

        /* --- Main Layout --- */
        .container {
            max-width: 680px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        /* --- Profile Section --- */
        .profile {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
        }

        .profile-title {
            font-size: 2.25em;
            font-weight: 700;
        }

        .profile-subtitle {
            font-size: 1.1em;
            color: var(--text-color);
            opacity: 0.8;
            margin-top: 4px;
        }

        /* --- Links Section --- */
        .links {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .link-button {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px 20px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1em;
            width: 100%;
            border-radius: var(--button-border-radius);
            box-shadow: var(--button-shadow);
            transition: transform 0.2s ease, background-color 0.2s ease;
            position: relative;
        }

        .link-button .icon {
            margin-right: 12px;
            font-size: 1.2em;
        }
        
        /* Conditional Button Styling */
        <?php if ($button_style === 'outline'): ?>
        .link-button {
            background-color: transparent;
            color: var(--button-text-color);
            border: var(--button-border-width) solid var(--button-bg-color);
        }
        .link-button:hover {
            background-color: var(--button-hover-bg);
            transform: var(--button-hover-transform);
        }
        <?php else: // Default to 'filled' style ?>
        .link-button {
            background-color: var(--button-bg-color);
            color: var(--button-text-color);
            border: none;
        }
        .link-button:hover {
            background-color: var(--button-hover-bg);
            transform: var(--button-hover-transform);
        }
        <?php endif; ?>

        /* --- Social Media Links Section --- */
        .socials {
            display: flex;
            gap: 24px;
            margin-top: 20px;
        }
        
        .social-icon {
            color: var(--text-color);
            opacity: 0.7;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        
        .social-icon:hover {
            opacity: 1;
            color: var(--accent-color);
            transform: scale(1.1);
        }

    </style>
</head>
<body>

    <main class="container">

         <!-- Profile Information -->
        <header class="profile">
            <?php if ($profile_image_url): ?>
                <img src="<?= htmlspecialchars($profile_image_url) ?>" alt="Profile Picture for <?= $title ?>" class="profile-image">
            <?php endif; ?>

            <h1 class="profile-title"><?= $title ?></h1>
            <?php if (!empty($subtitle)): ?>
                <p class="profile-subtitle"><?= $subtitle ?></p>
            <?php endif; ?>
        </header>

        <!-- Main Links -->
        <section class="links">
            <?php if (!empty($data->main_links) && is_array($data->main_links)): ?>
                <?php foreach ($data->main_links as $link): ?>
                    <a href="<?= htmlspecialchars(get_config($link, 'url', '#')) ?>" class="link-button" target="_blank" rel="noopener noreferrer">
                        <?php if ($icon = get_config($link, 'icon')): ?>
                            <span class="icon"><?= htmlspecialchars($icon) ?></span>
                        <?php endif; ?>
                        <span><?= htmlspecialchars(get_config($link, 'label', 'Link')) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <!-- Social Media Links -->
        <footer class="socials">
            <?php if (!empty($data->social_media_links) && is_array($data->social_media_links)): ?>
                <?php foreach ($data->social_media_links as $social): ?>
                    <a href="<?= htmlspecialchars(get_config($social, 'url', '#')) ?>" class="social-icon" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars(get_config($social, 'name')) ?>">
                        <?= get_social_icon(get_config($social, 'name')) ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </footer>

    </main>

</body>
</html>