<!DOCTYPE html>
<html lang="<?php echo $_SERVER['LOCALE']; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $title . ' - ' . $_SERVER['APPNAME']; ?></title>
    <meta name="description" content="<?php echo $meta_description; ?>">
    <?php if(isset($leaflet)): hasLeaflet('head'); endif; ?>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Loom73" />
    <link rel="manifest" href="/assets/favicon/site.webmanifest" />
    <!-- Preload Fonts -->
    <link rel="preload" href="/assets/fonts/IBM-Plex-Sans_latin_100_700_normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/assets/fonts/IBM-Plex-Sans_latin_100_700_italic.woff2" as="font" type="font/woff2" crossorigin>
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="/css/main.min.css">
</head>

<body class="<?php echo $bodyClass; ?>">
<a class="skip-link" href="#main-content">
    Skip to main content
</a>
