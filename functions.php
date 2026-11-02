<?php
/**
 * Edge Expos theme functions.
 *
 * @package Edge_Expos
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once get_theme_file_path('inc/blocks.php');
require_once get_theme_file_path('inc/sections.php');
require_once get_theme_file_path('inc/content.php');
require_once get_theme_file_path('inc/portfolio.php');
require_once get_theme_file_path('inc/contact-form.php');

function edgeexpo_setup(): void
{
    add_theme_support('wp-block-styles');
    add_theme_support('editor-styles');
    add_theme_support('post-thumbnails');
    add_editor_style(array('assets/css/frontend.css', 'assets/css/editor.css'));

    register_block_pattern_category(
        'edgeexpo-sections',
        array(
            'label' => __('Edge Expos Sections', 'edgeexpo'),
        )
    );
}
add_action('after_setup_theme', 'edgeexpo_setup');

function edgeexpo_enqueue_assets(): void
{
    $version = wp_get_theme()->get('Version');

    wp_enqueue_style('edgeexpo-frontend', get_theme_file_uri('assets/css/frontend.css'), array(), $version);
}
add_action('wp_enqueue_scripts', 'edgeexpo_enqueue_assets');

/**
 * Block styles offered in the editor.
 */
function edgeexpo_register_block_styles(): void
{
    // Green tick list, used for "Project Feature" lists on portfolio items.
    register_block_style(
        'core/list',
        array(
            'name'  => 'ee-checks',
            'label' => __('Green ticks', 'edgeexpo'),
        )
    );
    // Gallery shown as one large image per row, like the old portfolio pages.
    register_block_style(
        'core/gallery',
        array(
            'name'  => 'ee-stacked',
            'label' => __('Stacked', 'edgeexpo'),
        )
    );
}
add_action('init', 'edgeexpo_register_block_styles');

/**
 * Cache-bust theme images and icons with the theme version.
 *
 * @param string $url  Resolved file URL.
 * @param string $file Path relative to the theme root.
 */
function edgeexpo_version_theme_images(string $url, string $file): string
{
    if (! preg_match('#^/?assets/(img|icons)/.+\.[a-z0-9]+$#i', $file)) {
        return $url;
    }

    return add_query_arg('ver', wp_get_theme()->get('Version'), $url);
}
add_filter('theme_file_uri', 'edgeexpo_version_theme_images', 10, 2);

/**
 * Preload the fonts used above the fold so the header renders without a font swap.
 */
function edgeexpo_preload_fonts(): void
{
    foreach (array('open-sans-latin-variable.woff2', 'roboto-latin-400.woff2') as $font) {
        printf(
            '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
            esc_url(get_theme_file_uri('assets/fonts/' . $font))
        );
    }
}
add_action('wp_head', 'edgeexpo_preload_fonts', 1);

/**
 * Site icon from the Edge Expo logo, until a Site Icon is chosen in Settings → General.
 */
function edgeexpo_site_icon(): void
{
    if (has_site_icon()) {
        return;
    }

    $version = wp_get_theme()->get('Version');
    $icon    = static fn (int $size): string => esc_url(add_query_arg('ver', $version, get_theme_file_uri("assets/favicon/icon-{$size}.png")));

    printf('<link rel="icon" href="%s" sizes="32x32">' . "\n", $icon(32));
    printf('<link rel="icon" href="%s" sizes="192x192">' . "\n", $icon(192));
    printf('<link rel="apple-touch-icon" href="%s">' . "\n", $icon(180));
    printf('<meta name="msapplication-TileImage" content="%s">' . "\n", $icon(270));
}
add_action('wp_head', 'edgeexpo_site_icon');
add_action('admin_head', 'edgeexpo_site_icon');
add_action('login_head', 'edgeexpo_site_icon');

/**
 * Theme image URL, for patterns.
 *
 * @param string $file File name inside assets/img.
 */
function edgeexpo_img(string $file): string
{
    return esc_url(get_theme_file_uri('assets/img/' . $file));
}

/**
 * Public contact details (March 2025 site), shared by the header, CTA and Contact page.
 */
function edgeexpo_contact(): array
{
    return array(
        'phones'   => array('079 254 9572', '082 330 4996'),
        'emails'   => array('cara@edgeexpos.co.za', 'kim@edgeexpos.co.za'),
        'facebook' => 'https://www.facebook.com/edgeexpo',
        'workshops' => 'Johannesburg, Cape Town and Durban',
    );
}

/**
 * Main menu, in the 2025 order. One map shared by the header and the footer.
 */
function edgeexpo_menu(): array
{
    return array(
        array(
            'label'    => __('Exhibition Stands', 'edgeexpo'),
            'url'      => home_url('/'),
            'children' => array(
                array('label' => __('Custom', 'edgeexpo'), 'url' => home_url('/exhibition-stands/custom/')),
                array('label' => __('Modular', 'edgeexpo'), 'url' => home_url('/exhibition-stands/modular/')),
                array('label' => __('System', 'edgeexpo'), 'url' => home_url('/exhibition-stands/system/')),
            ),
        ),
        array('label' => __('Shop Fitting & Corporate Interior', 'edgeexpo'), 'url' => home_url('/shop-fitting-and-corporate-interiors/')),
        array('label' => __('Container Conversions', 'edgeexpo'), 'url' => home_url('/container-conversion-and-trailer-transformations/')),
        array('label' => __('Contact Us', 'edgeexpo'), 'url' => home_url('/contact-us/')),
    );
}

/**
 * /exhibition-stands/ only exists as the parent of the Custom, Modular and System pages.
 * The 2025 site showed Exhibition Stands on the home page, so send visitors there.
 */
function edgeexpo_redirect_stands_parent(): void
{
    if (is_page('exhibition-stands')) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
}
add_action('template_redirect', 'edgeexpo_redirect_stands_parent');

/**
 * Mark the menu item for the current page, and its dropdown parent, as current.
 *
 * The header menu uses custom links (see edgeexpo_menu()), which core does not flag as
 * current, so compare each link with the requested URL. On the front page the Exhibition
 * Stands item is current, as on the 2025 site.
 *
 * @param string $html  Rendered block.
 * @param array  $block Block data.
 */
function edgeexpo_nav_current(string $html, array $block): string
{
    $url = $block['attrs']['url'] ?? '';
    if (! $url || is_admin()) {
        return $html;
    }

    $here    = untrailingslashit(strtok(home_url(add_query_arg(array())), '?'));
    $target  = untrailingslashit($url);
    $current = $here === $target;

    if ('core/navigation-submenu' === $block['blockName']) {
        $children = wp_list_pluck(wp_list_pluck($block['innerBlocks'] ?? array(), 'attrs'), 'url');
        $children = array_map('untrailingslashit', array_filter($children));
        $current  = $current || in_array($here, $children, true);
    }

    if (! $current) {
        return $html;
    }

    $tags = new WP_HTML_Tag_Processor($html);
    if ($tags->next_tag('li')) {
        $tags->add_class('core/navigation-submenu' === $block['blockName'] ? 'current-menu-ancestor' : 'current-menu-item');
    }

    return $tags->get_updated_html();
}
add_filter('render_block_core/navigation-link', 'edgeexpo_nav_current', 10, 2);
add_filter('render_block_core/navigation-submenu', 'edgeexpo_nav_current', 10, 2);

/**
 * Home page title and description from the 2025 site (only when no SEO plugin handles them).
 *
 * @param string $title Document title.
 */
function edgeexpo_front_title(string $title): string
{
    return is_front_page() ? 'Exhibition Stands - Edge Expos - exhibition, stand, design' : $title;
}
add_filter('pre_get_document_title', 'edgeexpo_front_title');

function edgeexpo_front_description(): void
{
    if (! is_front_page() || defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) {
        return;
    }
    echo '<meta name="description" content="' . esc_attr__('Edge Expos design and build custom, modular and system exhibition stands, point of sale, shop fitting and container conversions, with workshops in Johannesburg, Cape Town and Durban.', 'edgeexpo') . '">' . "\n";
}
add_action('wp_head', 'edgeexpo_front_description', 2);
