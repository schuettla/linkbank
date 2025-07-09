<?php
// --- CONFIGURATION & INITIALIZATION ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Find all .json files in the current directory
$json_files = glob('*.json');
$selected_file = null;
$data = null;
$save_message = '';

// The list of available social platforms for the dropdown
$social_platforms = ['x', 'github', 'linkedin', 'instagram', 'email', 'facebook', 'tiktok', 'youtube', 'pinterest', 'threads'];
sort($social_platforms); // Keep them alphabetical


// --- LOGIC FOR SAVING DATA (POST REQUEST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_to_save = $_POST['file_to_save'] ?? null;

    // Security: ensure the file being saved is one of the allowed .json files
    if ($file_to_save && in_array($file_to_save, $json_files)) {
        
        // Reconstruct the JSON data structure from the $_POST array
        $new_data = [
            'profile_info' => $_POST['profile_info'] ?? [],
            'layout_config' => [
                'font' => $_POST['font'] ?? [],
                'colors' => $_POST['colors'] ?? [],
                'button' => $_POST['button'] ?? [],
                'hover_effects' => $_POST['hover_effects'] ?? [],
            ],
            // Use array_values to re-index arrays after items are removed
            'main_links' => isset($_POST['main_links']) ? array_values($_POST['main_links']) : [],
            'social_media_links' => isset($_POST['social_media_links']) ? array_values($_POST['social_media_links']) : []
        ];

        // Convert back to a nicely formatted JSON string
        $json_string = json_encode($new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Save the file
        if (file_put_contents($file_to_save, $json_string)) {
            $save_message = '<div class="message success">Successfully saved changes to <strong>' . htmlspecialchars($file_to_save) . '</strong>!</div>';
        } else {
            $save_message = '<div class="message error">Error: Could not write to file. Check file permissions.</div>';
        }
        
        // Set selected file to continue editing
        $selected_file = $file_to_save;

    } else {
         $save_message = '<div class="message error">Error: Invalid file specified for saving.</div>';
    }
}

// --- LOGIC FOR LOADING DATA TO EDIT (GET REQUEST) ---
$file_to_edit = $_GET['file'] ?? null;
if (!$selected_file && $file_to_edit) {
    // Security: ensure the file being loaded is a valid choice
    if (in_array($file_to_edit, $json_files)) {
        $selected_file = $file_to_edit;
    } else {
         $save_message = '<div class="message error">Error: Invalid file specified for editing.</div>';
    }
}

// If a file has been selected (either via GET or after a POST save), load its data
if ($selected_file) {
    $json_content = file_get_contents($selected_file);
    $data = json_decode($json_content);
}

// Helper function to safely get values from the loaded data object
function get_value($obj, $path, $default = '') {
    $keys = explode('.', $path);
    $value = $obj;
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

// Color fields definition for the loop
$color_fields = [
    'background' => 'Background',
    'text' => 'Text Color',
    'accent' => 'Accent Color',
    'button' => 'Button Color',
    'button_text' => 'Button Text Color',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page Editor</title>
    <style>
        :root {
            --bg-color: #f9fafb; --card-bg: #ffffff; --border-color: #e5e7eb;
            --text-color: #1f2937; --label-color: #374151; --accent-color: #4f46e5;
            --danger-color: #ef4444; --success-color: #10b981;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 2rem;
        }
        .container { max-width: 800px; margin: 0 auto; }
        h1, h2 { color: var(--text-color); }
        h1 { text-align: center; margin-bottom: 1rem; }
        .file-selector, .editor-form {
            background-color: var(--card-bg);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .file-selector-box { display: flex; gap: 1rem; align-items: center; }
        fieldset {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem 1.5rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        legend { font-size: 1.2rem; font-weight: 600; padding: 0 0.5rem; color: var(--accent-color); }
        .form-group { margin-bottom: 0.75rem; }
        .form-group:last-child { margin-bottom: 0; }
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.4rem;
            color: var(--label-color);
        }
        .form-group input[type="text"], .form-group input[type="url"], .form-group select {
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .form-group input:focus { border-color: var(--accent-color); outline: none; }
        
        .color-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        .color-input-group { display: flex; align-items: center; gap: 0.75rem; }
        .color-input-group label { margin: 0; font-weight: 600; width: 120px; }
        .color-input-group input[type="color"] {
            min-width: 36px;
            height: 36px;
            padding: 0;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .color-input-group input[type="text"] { flex-grow: 1; }
        
        .link-item, .social-item {
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            display: grid;
            gap: 0.75rem 1rem;
            align-items: flex-end;
        }
        .link-item { grid-template-columns: 1fr 1fr 120px; }
        .link-item .full-width { grid-column: 1 / 4; }
        .social-item { grid-template-columns: 1fr 2fr auto; }

        button {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            white-space: nowrap;
        }
        .btn-primary { background-color: var(--accent-color); color: white; }
        .btn-primary:hover { background-color: #4338ca; }
        .btn-secondary { background-color: #e5e7eb; color: #374151; }
        .btn-secondary:hover { background-color: #d1d5db; }
        .btn-danger { background-color: #fee2e2; color: var(--danger-color); }
        .btn-danger:hover { background-color: #fecaca; }
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500; }
        .message.success { background-color: #d1fae5; color: #065f46; }
        .message.error { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Profile Page Editor</h1>
        <?= $save_message ?>

        <div class="file-selector">
            <form method="GET" action="edit.php" class="file-selector-box">
                <label for="file"><strong>Editing File:</strong></label>
                <select id="file" name="file" style="flex-grow: 1;">
                    <?php if (empty($json_files)): ?>
                        <option disabled>No .json files found</option>
                    <?php else: ?>
                        <?php foreach ($json_files as $file): ?>
                            <option value="<?= htmlspecialchars($file) ?>" <?= ($file === $selected_file) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($file) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <button type="submit" class="btn-primary">Load</button>
            </form>
        </div>

        <?php if ($data): ?>
        <form class="editor-form" method="POST" action="edit.php">
            <input type="hidden" name="file_to_save" value="<?= htmlspecialchars($selected_file) ?>">
            
            <fieldset>
                <legend>Profile Info</legend>
                <div class="form-group"><label for="title">Title</label><input type="text" id="title" name="profile_info[title]" value="<?= htmlspecialchars(get_value($data, 'profile_info.title')) ?>"></div>
                <div class="form-group"><label for="subtitle">Subtitle</label><input type="text" id="subtitle" name="profile_info[subtitle]" value="<?= htmlspecialchars(get_value($data, 'profile_info.subtitle')) ?>"></div>
                <div class="form-group"><label for="profile_image">Profile Image URL</label><input type="url" id="profile_image" name="profile_info[profile_image]" value="<?= htmlspecialchars(get_value($data, 'profile_info.profile_image')) ?>"></div>
            </fieldset>

            <fieldset>
                <legend>Layout & Styling</legend>
                <div class="color-grid">
                    <?php foreach($color_fields as $key => $label): 
                        $current_val = htmlspecialchars(get_value($data, 'layout_config.colors.'.$key, ($key === 'background' ? '#ffffff' : '#000000')));
                        $is_complex = str_contains($current_val, 'gradient');
                    ?>
                    <div class="form-group">
                        <label><?= $label ?></label>
                        <div class="color-input-group">
                            <input type="color" value="<?= $is_complex ? '#ffffff' : $current_val ?>" oninput="this.nextElementSibling.value = this.value" <?= $is_complex ? 'disabled' : '' ?>>
                            <input type="text" name="colors[<?= $key ?>]" value="<?= $current_val ?>" onchange="this.previousElementSibling.value = this.value; this.previousElementSibling.disabled = (this.value.includes('gradient'))">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 1.5rem 0;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="button_style">Button Style</label>
                        <select id="button_style" name="button[style]">
                            <option value="filled" <?= get_value($data, 'layout_config.button.style') == 'filled' ? 'selected' : '' ?>>Filled</option>
                            <option value="outline" <?= get_value($data, 'layout_config.button.style') == 'outline' ? 'selected' : '' ?>>Outline</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Button Border Radius</label>
                        <input type="text" name="button[border_radius]" placeholder="e.g., 12px" value="<?= htmlspecialchars(get_value($data, 'layout_config.button.border_radius')) ?>">
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Main Links</legend>
                <div id="main-links-container">
                    <?php if (get_value($data, 'main_links')): foreach (get_value($data, 'main_links') as $i => $link): ?>
                    <div class="link-item">
                        <div class="form-group full-width"><label>Label</label><input type="text" name="main_links[<?= $i ?>][label]" value="<?= htmlspecialchars($link->label ?? '') ?>"></div>
                        <div class="form-group"><label>URL</label><input type="url" name="main_links[<?= $i ?>][url]" value="<?= htmlspecialchars($link->url ?? '') ?>"></div>
                        <div class="form-group"><label>Icon</label><input type="text" name="main_links[<?= $i ?>][icon]" value="<?= htmlspecialchars($link->icon ?? '') ?>"></div>
                        <button type="button" class="btn-danger" onclick="this.parentElement.remove()">Remove</button>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
                <button type="button" class="btn-secondary" onclick="addMainLink()">+ Add Link</button>
            </fieldset>

            <fieldset>
                <legend>Social Media Links</legend>
                <div id="social-links-container">
                    <?php if (get_value($data, 'social_media_links')): foreach (get_value($data, 'social_media_links') as $i => $link): ?>
                    <div class="social-item">
                        <div class="form-group">
                            <label>Platform</label>
                            <select name="social_media_links[<?= $i ?>][name]">
                                <?php foreach($social_platforms as $platform): ?>
                                <option value="<?= $platform ?>" <?= ($link->name ?? '') == $platform ? 'selected' : '' ?>><?= ucfirst($platform) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group"><label>URL</label><input type="url" name="social_media_links[<?= $i ?>][url]" value="<?= htmlspecialchars($link->url ?? '') ?>"></div>
                        <button type="button" class="btn-danger" onclick="this.parentElement.remove()">Remove</button>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
                <button type="button" class="btn-secondary" onclick="addSocialLink()">+ Add Social Link</button>
            </fieldset>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 1rem; font-size: 1.2rem;">Save Changes</button>
        </form>
        <?php endif; ?>
    </div>

<script>
    let mainLinkIndex = <?= count((array)get_value($data, 'main_links', [])) ?>;
    let socialLinkIndex = <?= count((array)get_value($data, 'social_media_links', [])) ?>;

    function addMainLink() {
        mainLinkIndex++;
        const container = document.getElementById('main-links-container');
        const template = `
            <div class="link-item">
                <div class="form-group full-width"><label>Label</label><input type="text" name="main_links[${mainLinkIndex}][label]" placeholder="My New Link"></div>
                <div class="form-group"><label>URL</label><input type="url" name="main_links[${mainLinkIndex}][url]" placeholder="https://example.com"></div>
                <div class="form-group"><label>Icon</label><input type="text" name="main_links[${mainLinkIndex}][icon]" placeholder="✨"></div>
                <button type="button" class="btn-danger" onclick="this.parentElement.remove()">Remove</button>
            </div>`;
        container.insertAdjacentHTML('beforeend', template);
    }

    function addSocialLink() {
        socialLinkIndex++;
        const container = document.getElementById('social-links-container');
        const platformOptions = `<?php foreach($social_platforms as $platform): ?><option value="<?= $platform ?>"><?= ucfirst($platform) ?></option><?php endforeach; ?>`;
        const template = `
            <div class="social-item">
                <div class="form-group"><label>Platform</label><select name="social_media_links[${socialLinkIndex}][name]">${platformOptions}</select></div>
                <div class="form-group"><label>URL</label><input type="url" name="social_media_links[${socialLinkIndex}][url]" placeholder="https://example.com/username"></div>
                <button type="button" class="btn-danger" onclick="this.parentElement.remove()">Remove</button>
            </div>`;
        container.insertAdjacentHTML('beforeend', template);
    }
</script>
</body>
</html>