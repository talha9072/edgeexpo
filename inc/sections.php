<?php
/**
 * Shared section markup used by several patterns: title bar, intro, feature boxes,
 * portfolio strip and the contact band.
 *
 * Each function returns block markup (see inc/blocks.php), so every section stays editable
 * in the editor once a pattern is inserted into a page.
 *
 * @package Edge_Expos
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Grey title bar for inner pages: page name left, breadcrumb right.
 *
 * @param string $title Page title (HTML allowed).
 * @param string $crumb Parent crumb label, or ''.
 */
function edgeexpo_title_bar(string $title, string $crumb = ''): string
{
    $trail = '<a href="' . esc_url(home_url('/')) . '">' . __('Home', 'edgeexpo') . '</a>';
    if ($crumb) {
        $trail .= ' / ' . $crumb;
    }
    $trail .= ' / ' . $title;

    return edgeexpo_block_group(
        'ee-titlebar',
        edgeexpo_block_group(
            'ee-container ee-titlebar__inner',
            edgeexpo_block_h(1, 'ee-titlebar__title', $title)
            . edgeexpo_block_p('ee-titlebar__crumbs', $trail)
        ),
        'div',
        array('align' => 'full')
    );
}

/**
 * Centred intro: uppercase heading, lead paragraphs, dashed separator, optional subhead.
 *
 * @param string $heading Heading HTML.
 * @param array  $leads   Paragraph HTML.
 * @param string $subhead Bold line under the separator.
 * @param int    $level   Heading level (1 on the home page, 2 under a title bar).
 */
function edgeexpo_intro(string $heading, array $leads, string $subhead = '', int $level = 2): string
{
    $inner = edgeexpo_block_h($level, 'ee-intro__title', $heading);
    foreach ($leads as $lead) {
        $inner .= edgeexpo_block_p('ee-intro__lead', $lead);
    }
    $inner .= edgeexpo_block_rule('ee-separator');
    if ($subhead) {
        $inner .= edgeexpo_block_p('ee-intro__subhead', $subhead);
    }

    return edgeexpo_block_section('ee-intro', $inner);
}

/**
 * Three feature boxes, each with a round icon, title and text.
 *
 * @param array $boxes array(icon, title, text) rows. Icon = file name in assets/icons without .svg.
 */
function edgeexpo_boxes(array $boxes): string
{
    $inner = '';
    foreach ($boxes as [$icon, $title, $text]) {
        $inner .= edgeexpo_block_group(
            'ee-box',
            edgeexpo_block_image('ee-box__icon', get_theme_file_uri('assets/icons/' . $icon . '.svg'))
            . edgeexpo_block_h(3, 'ee-box__title', $title)
            . edgeexpo_block_p('ee-box__text', $text)
        );
    }

    return edgeexpo_block_section('ee-boxes', edgeexpo_block_group('ee-grid ee-grid--3', $inner));
}

/**
 * Centred button row under a section.
 *
 * @param string $label Button label.
 * @param string $url   Button URL.
 */
function edgeexpo_button_row(string $label, string $url): string
{
    return edgeexpo_block_section('ee-button-row', edgeexpo_block_buttons('ee-buttons--center', array(array($label, $url))));
}

/**
 * Portfolio strip: the five most recent Portfolio items as square photos (Query Loop).
 *
 * Built from a live query so new portfolio items appear without editing the pages.
 */
function edgeexpo_portfolio_strip(int $count = 5): string
{
    $query = array(
        'queryId' => 21,
        'query'   => array(
            'perPage'  => $count,
            'pages'    => 1,
            'offset'   => 0,
            'postType' => EDGEEXPO_PORTFOLIO,
            'order'    => 'desc',
            'orderBy'  => 'date',
            'author'   => '',
            'search'   => '',
            'exclude'  => array(),
            'sticky'   => '',
            'inherit'  => false,
        ),
        'className' => 'ee-works',
    );

    $loop = sprintf(
        "<!-- wp:query%s -->\n<div class=\"wp-block-query ee-works\"><!-- wp:post-template%s -->\n<!-- wp:post-featured-image%s /-->\n<!-- /wp:post-template --></div>\n<!-- /wp:query -->",
        edgeexpo_block_attrs($query),
        edgeexpo_block_attrs(array('className' => 'ee-works__grid', 'layout' => array('type' => 'grid', 'columnCount' => $count))),
        edgeexpo_block_attrs(array('isLink' => true, 'aspectRatio' => '1', 'className' => 'ee-work'))
    );

    return edgeexpo_block_section('ee-portfolio-strip', $loop);
}

/**
 * Grey contact band with heading, text and Contact Us button.
 *
 * @param array|null $copy array(title, text) or null for the default wording.
 */
function edgeexpo_cta(?array $copy = null): string
{
    if ($copy) {
        [$title, $text] = $copy;
    } else {
        $default = edgeexpo_cta_copy();
        $title   = $default['title'];
        $text    = $default['text'];
    }

    $inner = edgeexpo_block_group(
        'ee-cta__box',
        edgeexpo_block_h(2, 'ee-cta__title', $title)
        . edgeexpo_block_p('ee-cta__text', $text)
        . edgeexpo_block_buttons('ee-buttons--center', array(array(__('Contact Us', 'edgeexpo'), home_url('/contact-us/'))))
    );

    return edgeexpo_block_section('ee-cta', $inner);
}

/**
 * "Featured project" row on a service page: a large photo of a real Edge Expos project
 * from that service, with a link to the project. Photos come from the Wayback Machine.
 *
 * @param string $key Service key from edgeexpo_page_content().
 */
function edgeexpo_feature(string $key): string
{
    $map = array(
        'custom'    => array('hurco', 'portfolio-hurco.webp', 'Hurco South Africa', 'A custom exhibition stand with a floating branded canopy, LED lighting and live machinery on display.'),
        'modular'   => array('fairtrade', 'portfolio-fairtrade.webp', 'Fairtrade Tourism', 'A double storey stand combining modular and custom elements, designed to travel between WTM Africa in Cape Town and Tourism Indaba in Durban.'),
        'system'    => array('dosco-precision-hydraulics', 'feature-dosco-precision-hydraulics.webp', 'Dosco Precision Hydraulics', 'A well lit, structurally stable corner stand at BAUMA Africa, built to display extremely heavy hydraulic pumps.'),
        'pos'       => array('barclays', 'feature-barclays.webp', 'Barclays', 'Custom designed point of sale: The Maze Game, with a CNC cut maze, duco spray finish and a weighted powder coated steel base.'),
        'shop'      => array('russell-athletic', 'feature-russell-athletic.webp', 'Russell Athletic', 'Custom shopfitting for a retail outlet in a local gym.'),
        'container' => array('mms-mobile-cranes', 'feature-mms-mobile-cranes.webp', 'MMS Mobile Cranes', 'A hydraulic container stand for BAUMA Africa that locks up every evening at the push of a button, with a Bedouin tent entertainment area and custom towers.'),
    );
    if (empty($map[$key])) {
        return '';
    }
    [$slug, $img, $name, $text] = $map[$key];
    $url = home_url('/portfolio-items/' . $slug . '/');

    $media = edgeexpo_block_image('ee-feature__image', edgeexpo_img($img), $name . ' by Edge Expos');
    $info  = edgeexpo_block_p('ee-feature__kicker', esc_html__('Featured project', 'edgeexpo'))
        . edgeexpo_block_h(3, 'ee-feature__title', esc_html($name))
        . edgeexpo_block_p('ee-feature__text', esc_html($text))
        . edgeexpo_block_buttons('ee-feature__buttons', array(array(__('View project', 'edgeexpo'), $url)));

    $columns = sprintf(
        "<!-- wp:columns {\"verticalAlignment\":\"center\",\"className\":\"ee-feature\"} -->\n<div class=\"wp-block-columns are-vertically-aligned-center ee-feature\"><!-- wp:column {\"verticalAlignment\":\"center\",\"width\":\"55%%\"} -->\n<div class=\"wp-block-column is-vertically-aligned-center\" style=\"flex-basis:55%%\">%s</div>\n<!-- /wp:column -->\n\n<!-- wp:column {\"verticalAlignment\":\"center\",\"width\":\"45%%\"} -->\n<div class=\"wp-block-column is-vertically-aligned-center\" style=\"flex-basis:45%%\">%s</div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->",
        $media,
        $info
    );

    return edgeexpo_block_section('ee-feature-section', $columns);
}

/**
 * Full block markup for one service-style page.
 *
 * @param string $key Key from edgeexpo_page_content().
 */
function edgeexpo_service_page(string $key): string
{
    $c   = edgeexpo_page_content($key);
    $out = '';

    if ($c['title']) {
        $out .= edgeexpo_title_bar($c['title'], $c['crumb']);
    }
    $out .= edgeexpo_intro($c['heading'], $c['leads'], $c['subhead'], $c['title'] ? 2 : 1);
    if ($c['boxes']) {
        $out .= edgeexpo_boxes($c['boxes']);
    }
    if ($c['button']) {
        $out .= edgeexpo_button_row($c['button'][0], $c['button'][1]);
    }
    $out .= edgeexpo_feature($key);
    $out .= edgeexpo_portfolio_strip();
    $out .= edgeexpo_cta($c['cta']);

    return $out;
}
