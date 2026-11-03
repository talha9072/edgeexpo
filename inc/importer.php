<?php
/**
 * One-click content import: creates the pages (from theme patterns) and the portfolio
 * projects, and sets the static front page. Safe to run again: existing pages and
 * projects are updated in place. Used by Appearance → Edge Expos Setup and by the local
 * dump/tools/setup.php helper.
 *
 * @package Edge_Expos
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Run the import and return its log.
 *
 * @param string $step pages | portfolio | all
 */
function edgeexpo_run_import(string $step = 'all'): string
{
    require_once ABSPATH . 'wp-admin/includes/post.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    if (function_exists('set_time_limit')) {
        set_time_limit(600);
    }
    ob_start();

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
            $local = get_theme_file_path('dump/wayback/files/') . $remote;
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

        // Download a photo from a full URL (e.g. the Facebook CDN) into the Media Library once.
        $import_url = static function (string $url, string $name, string $title): int {
            $name  = sanitize_title($name);
            $found = get_posts(array('post_type' => 'attachment', 'name' => $name, 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'inherit'));
            if ($found) {
                return $found[0];
            }
            $resp = wp_remote_get($url, array('timeout' => 40));
            if (is_wp_error($resp) || 200 !== wp_remote_retrieve_response_code($resp) || ! str_starts_with((string) wp_remote_retrieve_header($resp, 'content-type'), 'image/')) {
                echo "  photo failed: {$name}\n";
                return 0;
            }
            $upload = wp_upload_bits($name . '.jpg', null, wp_remote_retrieve_body($resp));
            if (! empty($upload['error'])) {
                echo '  upload error: ', $upload['error'], "\n";
                return 0;
            }
            $id = wp_insert_attachment(array('post_mime_type' => 'image/jpeg', 'post_title' => $title, 'post_name' => $name, 'post_status' => 'inherit'), $upload['file']);
            wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
            update_post_meta($id, '_wp_attachment_image_alt', $title . ' exhibition stand by Edge Expos');
            echo "  photo {$name}\n";
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

        $projects = json_decode((string) file_get_contents(get_theme_file_path('inc/data/portfolio.json')), true);
        $fbmap = (array) get_option('edgeexpo_fb_photos', array());
        foreach ($projects as $i => $project) {
            echo "project {$project['slug']}\n";
            if (! empty($fbmap[$project['slug']])) {
                $project['fb'] = $fbmap[$project['slug']];
            }

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
                $id = $import($extra, $project['found_fallback'] ?? null, $project['title']);
                if ($id) {
                    $images[] = $id;
                }
            }

            // Project photos from the Edge Expo Facebook album "Our Beautiful Stands - Portfolio".
            // The archive photo stays as the square thumbnail; the gallery shows the album photos.
            $fb_ids = array();
            foreach ($project['fb'] ?? array() as $n => $fb_url) {
                $fb_ids[] = $import_url($fb_url, 'portfolio-' . $project['slug'] . '-fb-' . ($n + 1), $project['title']);
            }
            $fb_ids  = array_values(array_filter($fb_ids));
            $thumb   = $images[0] ?? ($fb_ids[0] ?? 0);
            $gallery_ids = $fb_ids ? array_merge(array_slice($images, $project['thumb'] ? 1 : 0), $fb_ids) : $images;
            if ($fb_ids && ! empty($project['found'])) {
                // Keep renders found in the archive in the gallery as well.
                $gallery_ids = array_values(array_unique(array_merge($fb_ids, array_slice($images, $project['thumb'] ? 1 : 0))));
            }
            $images_for_thumb = $thumb;
            // At most 4 photos per project, spread across the set so the angles differ.
            if (count($gallery_ids) > 4) {
                $last        = count($gallery_ids) - 1;
                $gallery_ids = array_values(array_unique(array_map(static fn ($k) => $gallery_ids[(int) round($k * $last / 3)], array(0, 1, 2, 3))));
            }
            $images = $gallery_ids;

            $gallery_inner = '';
            foreach ($images as $img) {
                $url            = wp_get_attachment_image_url($img, 'large');
                $alt            = esc_attr(get_post_meta($img, '_wp_attachment_image_alt', true));
                $gallery_inner .= "<!-- wp:image {\"id\":{$img},\"sizeSlug\":\"large\",\"linkDestination\":\"none\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"" . esc_url($url) . "\" alt=\"{$alt}\" class=\"wp-image-{$img}\"/></figure>\n<!-- /wp:image -->";
            }
            $gallery = "<!-- wp:gallery {\"columns\":1,\"linkTo\":\"none\",\"sizeSlug\":\"large\",\"className\":\"is-style-ee-stacked\"} -->\n<figure class=\"wp-block-gallery has-nested-images columns-1 is-cropped is-style-ee-stacked\">{$gallery_inner}</figure>\n<!-- /wp:gallery -->";

            $missing = $fb_ids ? 0 : count($project['images']) - count($images) + count($project['found'] ?? array());
            $note    = '';
            if (! $images) {
                $note = $p('[PLACEHOLDER] Project photos were not saved by the Wayback Machine. Edge Expos to supply photos for this project.', 'ee-placeholder');
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
                'post_status'  => $project['status'] ?? 'publish',
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
            if ($images_for_thumb) {
                set_post_thumbnail($id, $images_for_thumb);
            }
            wp_set_object_terms($id, $project['categories'], EDGEEXPO_PORTFOLIO_TAX);
            echo '  ', get_permalink($id), ($images ? '' : ' (no photo)'), "\n";
        }
        flush_rewrite_rules();
    }

    return (string) ob_get_clean();
}

/**
 * Appearance → Edge Expos Setup (administrators only).
 */
function edgeexpo_setup_menu(): void
{
    add_theme_page(__('Edge Expos Setup', 'edgeexpo'), __('Edge Expos Setup', 'edgeexpo'), 'manage_options', 'edgeexpo-setup', 'edgeexpo_setup_screen');
}
add_action('admin_menu', 'edgeexpo_setup_menu');

function edgeexpo_setup_screen(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }
    $log = '';
    if (isset($_POST['edgeexpo_test_email']) && check_admin_referer('edgeexpo_test_email')) {
        $email = sanitize_email(wp_unslash($_POST['edgeexpo_test_email']));
        update_option('edgeexpo_contact_test_email', is_email($email) ? $email : '');
    }
    if (isset($_POST['edgeexpo_fb_json']) && check_admin_referer('edgeexpo_fb')) {
        $raw  = json_decode(wp_unslash((string) $_POST['edgeexpo_fb_json']), true);
        $save = array();
        foreach ((array) $raw as $slug => $urls) {
            foreach ((array) $urls as $url) {
                $url  = esc_url_raw((string) $url);
                $host = (string) wp_parse_url($url, PHP_URL_HOST);
                if ($url && str_starts_with($url, 'https://') && (str_ends_with($host, '.fbcdn.net') || str_ends_with($host, '.facebook.com'))) {
                    $save[sanitize_title((string) $slug)][] = $url;
                }
            }
        }
        update_option('edgeexpo_fb_photos', $save, false);
        echo '<div class="notice notice-success"><p>' . esc_html(sprintf('Saved Facebook photo list: %d projects, %d photos.', count($save), array_sum(array_map('count', $save)))) . '</p></div>';
    }
    if (isset($_POST['edgeexpo_step']) && check_admin_referer('edgeexpo_setup')) {
        $step = sanitize_key(wp_unslash($_POST['edgeexpo_step']));
        if (in_array($step, array('all', 'pages', 'portfolio'), true)) {
            update_option('permalink_structure', '/%postname%/');
            edgeexpo_register_portfolio();
            $log = edgeexpo_run_import($step);
            flush_rewrite_rules();
        }
    }
    echo '<div class="wrap"><h1>' . esc_html__('Edge Expos Setup', 'edgeexpo') . '</h1>';
    echo '<p>' . esc_html__('Creates the site pages and the portfolio projects (with photos from the Wayback Machine where they still exist) and sets the home page. Running it again updates the pages and projects in place, so any edits made to them in the editor will be overwritten.', 'edgeexpo') . '</p>';
    echo '<form method="post">';
    wp_nonce_field('edgeexpo_setup');
    echo '<p><button class="button button-primary" name="edgeexpo_step" value="all">' . esc_html__('Import pages and portfolio', 'edgeexpo') . '</button> ';
    echo '<button class="button" name="edgeexpo_step" value="pages">' . esc_html__('Pages only', 'edgeexpo') . '</button> ';
    echo '<button class="button" name="edgeexpo_step" value="portfolio">' . esc_html__('Portfolio only', 'edgeexpo') . '</button></p></form>';
    echo '<h2>' . esc_html__('Facebook portfolio photos', 'edgeexpo') . '</h2>';
    echo '<form method="post" id="ee-fb-form">';
    wp_nonce_field('edgeexpo_fb');
    echo '<p><textarea name="edgeexpo_fb_json" rows="3" class="large-text code" placeholder="{&quot;project-slug&quot;:[&quot;https://...fbcdn.net/...jpg&quot;]}"></textarea></p><p><button class="button">' . esc_html__('Save photo list', 'edgeexpo') . '</button> ' . esc_html(sprintf('%d projects saved. Run "Portfolio only" afterwards to download them.', count((array) get_option('edgeexpo_fb_photos', array())))) . '</p></form>';
    $test = (string) get_option('edgeexpo_contact_test_email', '');
    echo '<h2>' . esc_html__('Contact form recipients', 'edgeexpo') . '</h2>';
    echo '<p>' . ($test
        ? sprintf(esc_html__('TEST MODE: enquiries go only to %s. Clear the box and save to send them to Cara and Kim again.', 'edgeexpo'), '<strong>' . esc_html($test) . '</strong>')
        : esc_html__('Enquiries go to cara@edgeexpos.co.za and kim@edgeexpos.co.za.', 'edgeexpo')) . '</p>';
    echo '<form method="post">';
    wp_nonce_field('edgeexpo_test_email');
    echo '<p><input type="email" class="regular-text" name="edgeexpo_test_email" value="' . esc_attr($test) . '" placeholder="' . esc_attr__('Test address (leave empty for normal use)', 'edgeexpo') . '"> <button class="button">' . esc_html__('Save', 'edgeexpo') . '</button></p></form>';
    if ($log) {
        echo '<h2>' . esc_html__('Log', 'edgeexpo') . '</h2><pre style="background:#fff;padding:12px;max-height:500px;overflow:auto">' . esc_html($log) . '</pre>';
    }
    echo '</div>';
}
