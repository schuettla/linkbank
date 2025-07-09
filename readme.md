# PHP Link-in-Bio Profile Page

A simple, self-contained, single-file PHP application that creates a beautiful, Linktree-style profile page. All content and styling are dynamically generated from a simple JSON file, which can be managed through a secure, built-in visual editor.

This project is designed for users who want a lightweight, fast, and fully customizable profile page without the need for a database, frameworks, or external build processes.


## Features

-   **Dynamic Content:** All profile information, links, and social media icons are pulled from a JSON file.
-   **Dynamic Styling:** All colors, fonts, button styles, and even background images are configured in the same JSON file, allowing for infinite visual themes.
-   **Visual Editor:** A secure, password-protected `edit.php` script provides a user-friendly interface to modify your profile's content and appearance without manually editing JSON.
-   **Self-Contained:** The entire profile page (`index.php`) and editor (`edit.php`) are single files with no external dependencies other than Google Fonts.
-   **Secure:**
    -   Editor access is protected by robust `.htaccess` authentication.
    -   Data files (`.json`) are shielded from direct web access.
-   **SEO Optimized:** Automatically generates meta tags for titles, descriptions, and social media sharing (Open Graph/Twitter Cards).
-   **Extensible:** Easy to add new social media icons or expand functionality.

## Installation and Setup

Follow these steps to get your profile page running in minutes.

### Prerequisites

-   A web server running PHP (version 7.4+ recommended).
-   Apache web server (for `.htaccess` functionality). If you use Nginx or another server, you will need to adapt the access control rules.

---

### Step 1: Upload Files

Upload the project files to a directory on your web server.

```
/your-web-directory/
├── .htaccess
├── index.php
├── edit.php
└── data.json
```

---

### Step 2: Set Up Security (CRITICAL)

This step is essential to protect your editor and data from unauthorized access.

#### Part A: Create the Password File (`.htpasswd`)

This file stores your editor's username and encrypted password. For maximum security, **it should be stored outside of your public web directory**.

1.  **Generate Credentials:** The easiest way is to use a trusted online tool like the [htpasswd Generator](https://www.htaccesstools.com/htpasswd-generator/).
    -   Enter your desired username (e.g., `admin`).
    -   Enter a strong password.
    -   Copy the resulting line of text (e.g., `admin:$apr1$...`).

2.  **Create the File:**
    -   Create a new file named `.htpasswd`.
    -   Paste the generated line into this file and save it.
    -   Upload this file to a **non-public directory** on your server (e.g., `/home/yourusername/` which is one level above `public_html`).

> **For advanced users:** If you have command-line access to your server, you can generate the file securely with the command: `htpasswd -c /path/to/your/.htpasswd your_username`

#### Part B: Configure the Access Rules (`.htaccess`)

The `.htaccess` file controls access. Replace the content of the `.htaccess` file in your project directory with the following:

```apache
# --- PROTECT THE EDITOR SCRIPT ---
# This block requires a password to access edit.php.
<Files "edit.php">
    AuthType Basic
    AuthName "Admin Area"
    # IMPORTANT: Update the path below!
    AuthUserFile "/full/server/path/to/your/.htpasswd"
    Require valid-user
</Files>

# --- PROTECT THE DATA FILES ---
# This block prevents anyone from viewing .json files in their browser.
<Files "*.json">
    Require all denied
</Files>
```

**You MUST update the `AuthUserFile` path.** To find the full server path:
1.  Temporarily create a file named `path.php` in your project directory.
2.  Add the code: `<?php echo getcwd(); ?>`
3.  Visit `yourdomain.com/your-directory/path.php`. It will display the full path.
4.  Use this path to update the `AuthUserFile` line in `.htaccess`.
5.  **Delete `path.php` when you are done.**

---

### Step 3: Check File Permissions

Your web server needs permission to write to your `.json` data files. Ensure that files like `data.json` are writable by PHP. A common permission setting is `664`.

---

## How to Use the Tool

### Viewing Your Profile

Simply navigate to your website where you uploaded the files. `index.php` is your public profile page.

-   **URL:** `https://yourdomain.com/your-directory/`

### Editing Your Profile

1.  Navigate to `edit.php` in your browser.
    -   **URL:** `https://yourdomain.com/your-directory/edit.php`

2.  Your browser will prompt you for the username and password you created in Step 2.

3.  Once logged in, you will see the visual editor interface:
    -   **Select File:** Choose which `.json` profile you want to edit.
    -   **Profile Info:** Edit your main title, subtitle, and image URL.
    -   **Layout & Styling:** Use color pickers and text fields to change the entire look of your page. You can use standard hex codes (`#ff0000`), or even complex CSS like `linear-gradient(...)` for the background.
    -   **Main Links:** Add, remove, and re-order your primary links.
    -   **Social Media Links:** Add links for your social platforms from a pre-defined list.
    -   **Save Changes:** Click the big "Save Changes" button at the bottom to write your updates to the JSON file. The changes will be live on your profile page instantly.


## Included Style Examples  <-- INSERT THE NEW SECTION HERE

To demonstrate the power of the JSON-based styling, this project can be used with several pre-configured `data.json` examples. Each file creates a unique and distinct visual theme for your profile page.

To use one of these styles, simply **rename the content of your `data.json` file** with the content from one of the examples below.

1.  **Influencer / Lifestyle Blogger Style**
    -   **Vibe:** Warm, modern, and inviting.
    -   **Features:** A vibrant pink-to-blue gradient background, a friendly "Poppins" font, and soft, pill-shaped buttons. Perfect for content creators and lifestyle brands.

2.  **Brutalism Style**
    -   **Vibe:** Raw, functional, and high-contrast.
    -   **Features:** Rejects conventional softness in favor of a stark off-white background, a pixelated monospaced font ("VT323"), and sharp-edged buttons with a jarring hover effect.

3.  **Tech / Nerd Style**
    -   **Vibe:** A classic dark-mode theme reminiscent of a code editor or terminal.
    -   **Features:** A dark background, the "Fira Code" programming font, and "terminal green" text. The buttons have a subtle neon glow effect that intensifies on hover.

4.  **Business Elegant Style**
    -   **Vibe:** Professional, sophisticated, and clean.
    -   **Features:** A professional abstract blue background image, the classic "Lora" serif font, and semi-transparent "frosted glass" buttons. Ideal for consultants, authors, or corporate professionals.

These examples serve as a great starting point. You can load any of them into the editor and tweak them to create your own unique style.


## Advanced Customization

### Creating Multiple Themes

You can manage multiple profile configurations easily.
1.  Simply copy `data.json` to a new file, for example, `dark-theme.json`.
2.  Use the "Select a file to edit" dropdown in the editor to load and modify your new theme.
3.  To make a different theme live, you can either rename it to `data.json` or modify `index.php` to load a different file by default.

### Adding New Social Media Icons

1.  **Edit `index.php`:** Open the file and find the `get_social_icon()` function. Add a new entry to the `$icons` array with the name of the new platform and its SVG code. Ensure the SVG is set to use `currentColor` for its fill or stroke to match your theme.
2.  **Edit `edit.php`:** Find the `$social_platforms` array near the top of the file and add the name of your new platform to the list. This will make it available in the editor's dropdown menu.