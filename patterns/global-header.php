<?php
/**
 * Title: Global Header
 * Slug: edgeexpo/global-header
 * Categories: edgeexpo-sections
 * Block Types: core/template-part/header
 * Inserter: no
 * Description: Grey contact bar, the Edge Expo logo on the "EDGE" background band, and the centred main menu.
 *
 * @package Edge_Expos
 */

$edgeexpo_contact = edgeexpo_contact();
$edgeexpo_emails  = array_map(
    static fn (string $email): string => '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>',
    $edgeexpo_contact['emails']
);
?>
<!-- wp:group {"align":"full","className":"ee-header","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ee-header"><!-- wp:group {"className":"ee-topbar","layout":{"type":"default"}} -->
<div class="wp-block-group ee-topbar"><!-- wp:group {"className":"ee-container ee-topbar__inner","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group ee-container ee-topbar__inner"><!-- wp:paragraph {"className":"ee-topbar__contact"} -->
<p class="ee-topbar__contact"><?php echo esc_html(implode(' or ', $edgeexpo_contact['phones'])); ?> <span class="ee-topbar__sep">|</span> <?php echo implode(' or ', $edgeexpo_emails); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></p>
<!-- /wp:paragraph -->

<!-- wp:social-links {"iconColor":"white","iconColorValue":"#FFFFFF","size":"has-small-icon-size","className":"is-style-logos-only ee-topbar__social"} -->
<ul class="wp-block-social-links has-small-icon-size has-icon-color is-style-logos-only ee-topbar__social"><!-- wp:social-link {"url":"<?php echo esc_url($edgeexpo_contact['facebook']); ?>","service":"facebook"} /-->

<!-- wp:social-link {"url":"https://www.instagram.com/","service":"instagram"} /--></ul>
<!-- /wp:social-links --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"ee-brand","layout":{"type":"default"}} -->
<div class="wp-block-group ee-brand"><!-- wp:image {"sizeSlug":"full","linkDestination":"custom","className":"ee-brand__logo"} -->
<figure class="wp-block-image size-full ee-brand__logo"><a href="<?php echo esc_url(home_url('/')); ?>"><img src="<?php echo edgeexpo_img('edge-expo-logo.webp'); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>" alt="<?php esc_attr_e('Edge Expos home', 'edgeexpo'); ?>"/></a></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"ee-navbar","layout":{"type":"default"}} -->
<div class="wp-block-group ee-navbar"><!-- wp:navigation {"overlayBackgroundColor":"white","overlayTextColor":"ink","className":"ee-nav","layout":{"type":"flex","justifyContent":"center","flexWrap":"wrap"}} -->
<?php foreach (edgeexpo_menu() as $edgeexpo_item) : ?>
<?php if (! empty($edgeexpo_item['children'])) : ?>
<!-- wp:navigation-submenu {"label":"<?php echo esc_attr($edgeexpo_item['label']); ?>","url":"<?php echo esc_url($edgeexpo_item['url']); ?>","kind":"custom"} -->
<?php foreach ($edgeexpo_item['children'] as $edgeexpo_child) : ?>
<!-- wp:navigation-link {"label":"<?php echo esc_attr($edgeexpo_child['label']); ?>","url":"<?php echo esc_url($edgeexpo_child['url']); ?>","kind":"custom"} /-->
<?php endforeach; ?>
<!-- /wp:navigation-submenu -->
<?php else : ?>
<!-- wp:navigation-link {"label":"<?php echo esc_attr($edgeexpo_item['label']); ?>","url":"<?php echo esc_url($edgeexpo_item['url']); ?>","kind":"custom","isTopLevelLink":true} /-->
<?php endif; ?>
<?php endforeach; ?>
<!-- /wp:navigation --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
