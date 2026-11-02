<?php
/**
 * Title: Global Footer
 * Slug: edgeexpo/global-footer
 * Categories: edgeexpo-sections
 * Block Types: core/template-part/footer
 * Inserter: no
 * Description: Slim charcoal footer bar with copyright and quick links (the 2025 site had no footer widgets).
 *
 * @package Edge_Expos
 */

$edgeexpo_links = array(
    array(__('Portfolio', 'edgeexpo'), home_url('/portfolio/')),
    array(__('Point of Sale', 'edgeexpo'), home_url('/point-of-sale/')),
    array(__('Contact Us', 'edgeexpo'), home_url('/contact-us/')),
);
$edgeexpo_links_html = implode(
    ' <span class="ee-footer__dot">·</span> ',
    array_map(static fn ($l) => '<a href="' . esc_url($l[1]) . '">' . esc_html($l[0]) . '</a>', $edgeexpo_links)
);
?>
<!-- wp:group {"align":"full","className":"ee-footer","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ee-footer"><!-- wp:group {"className":"ee-container ee-footer__inner","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group ee-container ee-footer__inner"><!-- wp:paragraph {"className":"ee-footer__copy"} -->
<p class="ee-footer__copy">© <?php echo esc_html(gmdate('Y')); ?> Edge Expos. <?php esc_html_e('From concept to completion.', 'edgeexpo'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"ee-footer__links"} -->
<p class="ee-footer__links"><?php echo $edgeexpo_links_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
