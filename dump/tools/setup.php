<?php
// Local setup for the Edge Expos theme: activates the theme, sets permalinks, creates the pages
// from theme patterns, imports the portfolio and sets the static front page. Safe to run again.
// Local helper only: callable over HTTP on the .local site.
// usage: http://edgeexpo.local/wp-content/themes/edgeexpo/dump/tools/setup.php?step=all
//        steps: theme | pages | portfolio | all
require dirname(__DIR__, 5) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/post.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

if (! str_ends_with((string) wp_parse_url(home_url(), PHP_URL_HOST), '.local')) {
    exit('local only');
}
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(300);

$step = $_GET['step'] ?? 'all';

if (in_array($step, array('all', 'theme'), true)) {
    switch_theme('edgeexpo');
    update_option('permalink_structure', '/%postname%/');
    update_option('blogname', 'Edge Expos');
    update_option('blogdescription', 'From concept to completion');
    // The theme's functions.php is only loaded on the next request after switch_theme().
    if (function_exists('edgeexpo_register_portfolio')) {
        edgeexpo_register_portfolio();
    }
    flush_rewrite_rules();
    if ('all' === $step && ! function_exists('edgeexpo_register_portfolio')) {
        echo "theme switched — run this script again to create the pages and portfolio\n";
        exit;
    }
    echo 'theme: ', get_stylesheet(), "\n";
}

echo edgeexpo_run_import($step === 'theme' ? 'none' : $step);
