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

// slug => array(title, patterns, template, parent slug)
$pages = array(
    'home'                                            => array('Exhibition Stands', 'home-hero,home-intro,home-principles,global-portfolio-strip,global-contact-band'),
    'exhibition-stands'                               => array('Exhibition Stands', 'home-intro'),
    'custom'                                          => array('Custom', 'service-custom', '', 'exhibition-stands'),
    'modular'                                         => array('Modular', 'service-modular', '', 'exhibition-stands'),
    'system'                                          => array('System', 'service-system', '', 'exhibition-stands'),
    'point-of-sale'                                   => array('Point of Sale', 'service-point-of-sale'),
    'shop-fitting-and-corporate-interiors'            => array('Shop Fitting & Corporate Interior', 'service-shop-fitting'),
    'container-conversion-and-trailer-transformations' => array('Container Conversions', 'service-container-conversions'),
    'contact-us'                                      => array('Contact Us', 'contact-page'),
    'thank-you'                                       => array('Thank You', 'status-thank-you', 'page-thank-you'),
);

if (in_array($step, array('all', 'pages'), true)) {
    $ids = array();
    foreach ($pages as $slug => $page) {
        $content  = implode("\n\n", array_map(
            static fn ($p) => '<!-- wp:pattern {"slug":"edgeexpo/' . $p . '"} /-->',
            explode(',', $page[1])
        ));
        $parent   = ! empty($page[3]) ? ($ids[$page[3]] ?? 0) : 0;
        $existing = get_page_by_path($parent ? $page[3] . '/' . $slug : $slug);
        $args     = array(
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_name'    => $slug,
            'post_title'   => $page[0],
            'post_content' => $content,
            'post_parent'  => $parent,
        );
        if ($existing) {
            $args['ID'] = $existing->ID;
        }
        $id = wp_insert_post(wp_slash($args), true);
        if (! is_wp_error($id)) {
            $ids[$slug] = $id;
            update_post_meta($id, '_wp_page_template', $page[2] ?? '');
        }
        echo is_wp_error($id) ? 'ERROR ' . $id->get_error_message() : 'page ' . $id . ' ' . get_permalink($id), "\n";
    }

    update_option('show_on_front', 'page');
    update_option('page_on_front', $ids['home']);
    update_option('page_for_posts', 0);

    foreach (array(get_page_by_path('sample-page'), get_page_by_path('hello-world', OBJECT, 'post'), get_page_by_path('privacy-policy')) as $default) {
        if ($default && 'trash' !== $default->post_status) {
            wp_trash_post($default->ID);
        }
    }
    echo "front page: {$ids['home']}\n";
}

if (in_array($step, array('all', 'portfolio'), true)) {
    edgeexpo_portfolio_default_terms();

    // Full-size originals still in the Wayback Machine; the theme's small copies are the fallback.
    $wayback = 'https://web.archive.org/web/2025im_/https://www.edgeexpo.co.za/wp-content/uploads/';
    $import  = static function (string $remote, ?string $fallback, string $title) use ($wayback): int {
        $name  = sanitize_title('portfolio-' . pathinfo($remote, PATHINFO_FILENAME));
        $found = get_posts(array('post_type' => 'attachment', 'name' => $name, 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'inherit'));
        // ?retry=1: replace small fallback copies (webp) with the full-size archive original.
        if ($found && isset($_GET['retry']) && 'image/webp' === get_post_mime_type($found[0]) && 'webp' !== pathinfo($remote, PATHINFO_EXTENSION)) {
            wp_delete_attachment($found[0], true);
            $found = array();
        }
        if ($found) {
            return $found[0];
        }
        $bytes = '';
        // Prefer a copy already fetched by wayback.php (dump/wayback/files/).
        $local = dirname(__DIR__) . '/wayback/files/' . $remote;
        if (is_readable($local) && filesize($local) > 0) {
            $bytes = (string) file_get_contents($local);
            $file  = $name . '.' . pathinfo($remote, PATHINFO_EXTENSION);
            echo "  local copy {$remote} (" . strlen($bytes) . " bytes)\n";
            $resp = null;
        } else {
            $resp = wp_remote_get($wayback . $remote, array('timeout' => 40));
        }
        if ('' !== $bytes) {
            // Already loaded from the local copy.
        } elseif (! is_wp_error($resp) && 200 === wp_remote_retrieve_response_code($resp) && str_starts_with((string) wp_remote_retrieve_header($resp, 'content-type'), 'image/')) {
            $bytes = wp_remote_retrieve_body($resp);
            $file  = $name . '.' . pathinfo($remote, PATHINFO_EXTENSION);
            echo "  downloaded {$remote} (" . strlen($bytes) . " bytes)\n";
        } elseif ($fallback) {
            $bytes = (string) file_get_contents(get_theme_file_path('assets/img/' . $fallback));
            $file  = $name . '.webp';
            echo "  wayback failed for {$remote}, used theme copy {$fallback}\n";
        } else {
            return 0;
        }
        $upload = wp_upload_bits($file, null, $bytes);
        if (! empty($upload['error'])) {
            echo '  upload error: ', $upload['error'], "\n";
            return 0;
        }
        $type = wp_check_filetype($upload['file']);
        $id   = wp_insert_attachment(array('post_mime_type' => $type['type'], 'post_title' => $title, 'post_name' => $name, 'post_status' => 'inherit'), $upload['file']);
        wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
        update_post_meta($id, '_wp_attachment_image_alt', $title . ' exhibition stand by Edge Expos');
        return $id;
    };

    $esc_block = static fn (string $s): string => wp_kses_post($s);
    $p         = static fn (string $html, string $class = ''): string => $class
        ? "<!-- wp:paragraph {\"className\":\"{$class}\"} -->\n<p class=\"{$class}\">{$html}</p>\n<!-- /wp:paragraph -->"
        : "<!-- wp:paragraph -->\n<p>{$html}</p>\n<!-- /wp:paragraph -->";
    $list      = static function (array $items): string {
        $li = '';
        foreach ($items as $item) {
            $li .= "<!-- wp:list-item -->\n<li>" . esc_html($item) . "</li>\n<!-- /wp:list-item -->";
        }
        return "<!-- wp:list {\"className\":\"is-style-ee-checks\"} -->\n<ul class=\"wp-block-list is-style-ee-checks\">{$li}</ul>\n<!-- /wp:list -->";
    };

    $projects = json_decode((string) file_get_contents(__DIR__ . '/portfolio.json'), true);
    foreach ($projects as $i => $project) {
        echo "project {$project['slug']}\n";

        // Only the thumbnail photos survived in the archive; gallery photos were never captured.
        $images = array();
        if ($project['thumb']) {
            $id = $import($project['images'][0], $project['thumb'], $project['title']);
            if ($id) {
                $images[] = $id;
            }
        }
        // Other archived pictures of the same project (e.g. a stand render used on the old home slider).
        foreach ($project['found'] ?? array() as $extra) {
            $id = $import($extra, null, $project['title']);
            if ($id) {
                $images[] = $id;
            }
        }

        $gallery_inner = '';
        foreach ($images as $img) {
            $url            = wp_get_attachment_image_url($img, 'large');
            $alt            = esc_attr(get_post_meta($img, '_wp_attachment_image_alt', true));
            $gallery_inner .= "<!-- wp:image {\"id\":{$img},\"sizeSlug\":\"large\",\"linkDestination\":\"none\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"" . esc_url($url) . "\" alt=\"{$alt}\" class=\"wp-image-{$img}\"/></figure>\n<!-- /wp:image -->";
        }
        $gallery = "<!-- wp:gallery {\"columns\":1,\"linkTo\":\"none\",\"sizeSlug\":\"large\",\"className\":\"is-style-ee-stacked\"} -->\n<figure class=\"wp-block-gallery has-nested-images columns-1 is-cropped is-style-ee-stacked\">{$gallery_inner}</figure>\n<!-- /wp:gallery -->";

        $missing = count($project['images']) - count($images) + count($project['found'] ?? array());
        $note    = '';
        if (! $images) {
            $note = $p('[PLACEHOLDER] Project photos were not saved by the Wayback Machine. Edge Expos to supply photos for this project.', 'ee-placeholder');
        } elseif ($missing > 0) {
            $note = $p(sprintf('[PLACEHOLDER] %d more photo%s from the old site were not archived. Edge Expos to supply.', $missing, 1 === $missing ? '' : 's'), 'ee-placeholder');
        }

        $info = "<!-- wp:post-title {\"level\":1,\"className\":\"ee-project__title\"} /-->\n\n"
            . "<!-- wp:heading {\"className\":\"ee-project__event\"} -->\n<h2 class=\"wp-block-heading ee-project__event\">" . esc_html($project['event']) . "</h2>\n<!-- /wp:heading -->\n\n";
        foreach ($project['body'] as $para) {
            $info .= $p(esc_html($para)) . "\n\n";
        }
        if (! $project['body']) {
            $info .= $p('[PLACEHOLDER] The project description was not saved on the old site. Edge Expos to supply.', 'ee-placeholder') . "\n\n";
        }
        $info .= "<!-- wp:heading {\"level\":4,\"className\":\"ee-project__feature-title\"} -->\n<h4 class=\"wp-block-heading ee-project__feature-title\">Project Feature:</h4>\n<!-- /wp:heading -->\n\n" . $list($project['features']);
        if (! empty($project['extra'])) {
            $x     = $project['extra'];
            $info .= "\n\n<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">" . esc_html($x['title']) . "</h3>\n<!-- /wp:heading -->\n\n"
                . $p(esc_html($x['body'])) . "\n\n"
                . "<!-- wp:heading {\"level\":4,\"className\":\"ee-project__feature-title\"} -->\n<h4 class=\"wp-block-heading ee-project__feature-title\">Project Feature:</h4>\n<!-- /wp:heading -->\n\n" . $list($x['features']);
        }

        $content = "<!-- wp:columns {\"className\":\"ee-project\"} -->\n<div class=\"wp-block-columns ee-project\"><!-- wp:column {\"width\":\"62%\",\"className\":\"ee-project__media\"} -->\n<div class=\"wp-block-column ee-project__media\" style=\"flex-basis:62%\">"
            . $gallery . ($note ? "\n\n" . $note : '')
            . "</div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"width\":\"38%\",\"className\":\"ee-project__info\"} -->\n<div class=\"wp-block-column ee-project__info\" style=\"flex-basis:38%\">"
            . $info
            . "</div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->";

        $existing = get_page_by_path($project['slug'], OBJECT, EDGEEXPO_PORTFOLIO);
        $args     = array(
            'post_type'    => EDGEEXPO_PORTFOLIO,
            'post_status'  => 'publish',
            'post_name'    => $project['slug'],
            'post_title'   => $project['title'],
            'post_date'    => $project['date'],
            'post_excerpt' => $project['event'],
            'post_content' => $content,
            'menu_order'   => $i,
        );
        if ($existing) {
            $args['ID'] = $existing->ID;
        }
        $id = wp_insert_post(wp_slash($args), true);
        if (is_wp_error($id)) {
            echo '  ERROR ', $id->get_error_message(), "\n";
            continue;
        }
        if ($images) {
            set_post_thumbnail($id, $images[0]);
        }
        wp_set_object_terms($id, $project['categories'], EDGEEXPO_PORTFOLIO_TAX);
        echo '  ', get_permalink($id), ($images ? '' : ' (no photo)'), "\n";
    }
    flush_rewrite_rules();
}
