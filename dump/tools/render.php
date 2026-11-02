<?php
// Local helper: prints the registered content of every theme pattern, plus the saved content of
// every page and portfolio item, so the block validator can check them.
// usage: http://edgeexpo.local/wp-content/themes/edgeexpo/dump/tools/render.php
require dirname(__DIR__, 5) . '/wp-load.php';
if (! str_ends_with((string) wp_parse_url(home_url(), PHP_URL_HOST), '.local')) {
    exit('local only');
}
header('Content-Type: application/json; charset=utf-8');
$out = array();
foreach (WP_Block_Patterns_Registry::get_instance()->get_all_registered() as $pattern) {
    if (str_starts_with($pattern['name'], 'edgeexpo/')) {
        $out['pattern:' . $pattern['name']] = $pattern['content'];
    }
}
foreach (get_posts(array('post_type' => array('page', 'portfolio'), 'post_status' => 'publish', 'posts_per_page' => -1)) as $post) {
    $out[$post->post_type . ':' . $post->post_name] = $post->post_content;
}
echo wp_json_encode($out);
