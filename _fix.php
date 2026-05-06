<?php
$styleFile = 'assets/css/style.css';
$content = file_get_contents($styleFile);

// Update primary colors to richer Midnight Navy
$content = str_replace('--primary: #1e3b5a;', '--primary: #0f172a;', $content);
$content = str_replace('--primary-dark: #152a40;', '--primary-dark: #020617;', $content);
$content = str_replace('--primary-light: #2c5282;', '--primary-light: #1e293b;', $content);

// Update Gold scale to be more vibrant
$content = str_replace('--gold-400: #f9e4c8;', '--gold-400: #fef08a;', $content);
$content = str_replace('--gold-300: #e8d4a8;', '--gold-300: #fde047;', $content);
$content = str_replace('--gold-200: #d4b87a;', '--gold-200: #facc15;', $content);
$content = str_replace('--gold-100: #c8a663;', '--gold-100: #eab308;', $content);
$content = str_replace('--gold-50:  #a8853e;', '--gold-50:  #ca8a04;', $content);

// Update Fonts
$content = str_replace(
    "--font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;",
    "--font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;\r\n    --font-heading: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;",
    $content
);

// Update Shadows for premium glass effect
$content = preg_replace(
    "/--shadow-sm:.*?;/",
    "--shadow-sm: 0 2px 4px rgba(15, 23, 42, 0.05), 0 1px 2px rgba(15, 23, 42, 0.1);",
    $content
);
$content = preg_replace(
    "/--shadow-md:.*?;/",
    "--shadow-md: 0 4px 10px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.08);",
    $content
);
$content = preg_replace(
    "/--shadow-lg:.*?;/",
    "--shadow-lg: 0 14px 24px -4px rgba(15, 23, 42, 0.06), 0 6px 12px -2px rgba(15, 23, 42, 0.08);",
    $content
);
$content = preg_replace(
    "/--shadow-xl:.*?;/",
    "--shadow-xl: 0 24px 40px -8px rgba(15, 23, 42, 0.08), 0 10px 20px -4px rgba(15, 23, 42, 0.1);",
    $content
);

file_put_contents($styleFile, $content);

$homeFile = 'assets/css/home.css';
$homeContent = file_get_contents($homeFile);
$homeContent = str_replace('font-family:var(--font-family);', 'font-family:var(--font-heading);', $homeContent);
file_put_contents($homeFile, $homeContent);

echo "Styles updated successfully.";
