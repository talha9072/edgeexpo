<?php
/**
 * Portfolio post type and categories.
 *
 * Each project is a normal block-editor post: a new item opens with the old site's layout
 * already in place (gallery left; title, event, description and a green-tick "Project
 * Feature" list right). Everything in it is a core block, so the team can add photos to the
 * gallery, rewrite the text and add or remove list items without touching code.
 *
 * URLs match the old WordPress site: /portfolio-items/<project>/ for items and /portfolio/
 * for the listing, so links indexed by Google keep working.
 *
 * @package Edge_Expos
 */

if (! defined('ABSPATH')) {
    exit;
}

const EDGEEXPO_PORTFOLIO = 'portfolio';
const EDGEEXPO_PORTFOLIO_TAX = 'portfolio_category';

function edgeexpo_register_portfolio(): void
{
    register_post_type(
        EDGEEXPO_PORTFOLIO,
        array(
            'labels'        => array(
                'name'               => __('Portfolio', 'edgeexpo'),
                'singular_name'      => __('Project', 'edgeexpo'),
                'menu_name'          => __('Portfolio', 'edgeexpo'),
                'all_items'          => __('All Projects', 'edgeexpo'),
                'add_new'            => __('Add Project', 'edgeexpo'),
                'add_new_item'       => __('Add New Project', 'edgeexpo'),
                'edit_item'          => __('Edit Project', 'edgeexpo'),
                'new_item'           => __('New Project', 'edgeexpo'),
                'view_item'          => __('View Project', 'edgeexpo'),
                'view_items'         => __('View Portfolio', 'edgeexpo'),
                'search_items'       => __('Search Projects', 'edgeexpo'),
                'not_found'          => __('No projects found.', 'edgeexpo'),
                'not_found_in_trash' => __('No projects found in Trash.', 'edgeexpo'),
                'featured_image'     => __('Project thumbnail', 'edgeexpo'),
                'set_featured_image' => __('Set project thumbnail', 'edgeexpo'),
                'archives'           => __('Portfolio', 'edgeexpo'),
            ),
            'description'   => __('Exhibition stands, point of sale, shop fitting and container projects.', 'edgeexpo'),
            'public'        => true,
            'show_in_rest'  => true,
            'menu_position' => 20,
            'menu_icon'     => 'dashicons-format-gallery',
            'has_archive'   => 'portfolio',
            'rewrite'       => array(
                'slug'       => 'portfolio-items',
                'with_front' => false,
            ),
            'supports'      => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes'),
            'template'      => edgeexpo_portfolio_block_template(),
        )
    );

    register_taxonomy(
        EDGEEXPO_PORTFOLIO_TAX,
        EDGEEXPO_PORTFOLIO,
        array(
            'labels'            => array(
                'name'          => __('Project Categories', 'edgeexpo'),
                'singular_name' => __('Project Category', 'edgeexpo'),
                'menu_name'     => __('Categories', 'edgeexpo'),
                'all_items'     => __('All Categories', 'edgeexpo'),
                'edit_item'     => __('Edit Category', 'edgeexpo'),
                'add_new_item'  => __('Add New Category', 'edgeexpo'),
            ),
            'public'            => true,
            'hierarchical'      => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => array(
                'slug'       => 'portfolio-category',
                'with_front' => false,
            ),
        )
    );
}
add_action('init', 'edgeexpo_register_portfolio');

/**
 * Starting layout for a new project (matches the old Avada portfolio page).
 */
function edgeexpo_portfolio_block_template(): array
{
    return array(
        array(
            'core/columns',
            array('className' => 'ee-project'),
            array(
                array(
                    'core/column',
                    array('width' => '62%', 'className' => 'ee-project__media'),
                    array(
                        array('core/gallery', array('columns' => 1, 'linkTo' => 'none', 'sizeSlug' => 'large', 'className' => 'is-style-ee-stacked')),
                    ),
                ),
                array(
                    'core/column',
                    array('width' => '38%', 'className' => 'ee-project__info'),
                    array(
                        array('core/post-title', array('level' => 1, 'className' => 'ee-project__title')),
                        array('core/heading', array('level' => 2, 'className' => 'ee-project__event', 'placeholder' => __('Event or venue, e.g. BAUMA Africa: Johannesburg Expo Centre', 'edgeexpo'))),
                        array('core/paragraph', array('placeholder' => __('Describe the brief, the stand and the result…', 'edgeexpo'))),
                        array('core/heading', array('level' => 4, 'className' => 'ee-project__feature-title', 'content' => __('Project Feature:', 'edgeexpo'))),
                        array(
                            'core/list',
                            array('className' => 'is-style-ee-checks'),
                            array(
                                array('core/list-item', array('placeholder' => __('Feature, e.g. Custom Exhibition Stand', 'edgeexpo'))),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );
}

/**
 * Default categories, created once (the first time an editor visits wp-admin with the theme on).
 */
function edgeexpo_portfolio_default_terms(): void
{
    if (get_option('edgeexpo_portfolio_terms') || ! taxonomy_exists(EDGEEXPO_PORTFOLIO_TAX)) {
        return;
    }

    $stands = term_exists('exhibition-stands', EDGEEXPO_PORTFOLIO_TAX) ?: wp_insert_term(__('Exhibition Stands', 'edgeexpo'), EDGEEXPO_PORTFOLIO_TAX, array('slug' => 'exhibition-stands'));
    if (! is_wp_error($stands)) {
        foreach (array('custom' => __('Custom', 'edgeexpo'), 'modular' => __('Modular', 'edgeexpo'), 'system' => __('System', 'edgeexpo')) as $slug => $name) {
            if (! term_exists($slug, EDGEEXPO_PORTFOLIO_TAX)) {
                wp_insert_term($name, EDGEEXPO_PORTFOLIO_TAX, array('slug' => $slug, 'parent' => (int) $stands['term_id']));
            }
        }
    }
    foreach (
        array(
            'point-of-sale'        => __('Point of Sale', 'edgeexpo'),
            'shop-fitting'         => __('Shop Fitting & Corporate Interiors', 'edgeexpo'),
            'container-conversions' => __('Container & Trailer Conversions', 'edgeexpo'),
        ) as $slug => $name
    ) {
        if (! term_exists($slug, EDGEEXPO_PORTFOLIO_TAX)) {
            wp_insert_term($name, EDGEEXPO_PORTFOLIO_TAX, array('slug' => $slug));
        }
    }
    update_option('edgeexpo_portfolio_terms', 1);
}
add_action('admin_init', 'edgeexpo_portfolio_default_terms');

/**
 * Flush rewrite rules once after the theme is switched on, so /portfolio-items/ works straight away.
 */
function edgeexpo_portfolio_flush(): void
{
    edgeexpo_register_portfolio();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'edgeexpo_portfolio_flush');

/**
 * Listing: 12 projects per page, newest first.
 *
 * @param WP_Query $query Main query.
 */
function edgeexpo_portfolio_archive_query(WP_Query $query): void
{
    if (is_admin() || ! $query->is_main_query()) {
        return;
    }
    if ($query->is_post_type_archive(EDGEEXPO_PORTFOLIO) || $query->is_tax(EDGEEXPO_PORTFOLIO_TAX)) {
        $query->set('posts_per_page', 12);
    }
}
add_action('pre_get_posts', 'edgeexpo_portfolio_archive_query');

/**
 * Old project URLs that moved (Uniresins was /portfolio-items/uniresins-2/ on the old site).
 */
function edgeexpo_portfolio_old_urls(): void
{
    if (! is_404()) {
        return;
    }
    $path = trim((string) wp_parse_url(add_query_arg(array()), PHP_URL_PATH), '/');
    if (preg_match('#portfolio-items/uniresins-2$#', $path)) {
        wp_safe_redirect(home_url('/portfolio-items/uniresins/'), 301);
        exit;
    }
}
add_action('template_redirect', 'edgeexpo_portfolio_old_urls');

/**
 * Projects without a thumbnail still get a square tile in the listing and the portfolio
 * strip, so the grid keeps its shape until Edge Expos supply the photo.
 *
 * @param string   $html     Rendered block.
 * @param array    $block    Block data.
 * @param WP_Block $instance Block instance (for the post ID in the loop).
 */
function edgeexpo_featured_image_placeholder(string $html, array $block, WP_Block $instance): string
{
    if ('' !== trim($html)) {
        return $html;
    }
    $class = $block['attrs']['className'] ?? '';
    if (! preg_match('/\b(ee-card__image|ee-work)\b/', $class)) {
        return $html;
    }
    $post_id = $instance->context['postId'] ?? get_the_ID();

    return sprintf(
        '<figure class="wp-block-post-featured-image %s ee-noimg"><a href="%s"><span>%s</span></a></figure>',
        esc_attr($class),
        esc_url(get_permalink($post_id)),
        esc_html__('Photo coming soon', 'edgeexpo')
    );
}
add_filter('render_block_core/post-featured-image', 'edgeexpo_featured_image_placeholder', 10, 3);
