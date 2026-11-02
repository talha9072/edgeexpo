<?php
/**
 * Title: Project Layout
 * Slug: edgeexpo/portfolio-project-layout
 * Categories: edgeexpo-sections
 * Keywords: portfolio, project, gallery, features
 * Post Types: portfolio
 * Description: Gallery on the left; project title, event, description and a green-tick feature list on the right. New projects start with this layout already in place.
 *
 * @package Edge_Expos
 */

?>
<!-- wp:columns {"className":"ee-project"} -->
<div class="wp-block-columns ee-project"><!-- wp:column {"width":"62%","className":"ee-project__media"} -->
<div class="wp-block-column ee-project__media" style="flex-basis:62%"><!-- wp:gallery {"columns":1,"linkTo":"none","sizeSlug":"large","className":"is-style-ee-stacked"} -->
<figure class="wp-block-gallery has-nested-images columns-1 is-cropped is-style-ee-stacked"></figure>
<!-- /wp:gallery --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"38%","className":"ee-project__info"} -->
<div class="wp-block-column ee-project__info" style="flex-basis:38%"><!-- wp:post-title {"level":1,"className":"ee-project__title"} /-->

<!-- wp:heading {"className":"ee-project__event"} -->
<h2 class="wp-block-heading ee-project__event"><?php esc_html_e('Event: Venue', 'edgeexpo'); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e('Describe the brief, the stand and the result.', 'edgeexpo'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":4,"className":"ee-project__feature-title"} -->
<h4 class="wp-block-heading ee-project__feature-title"><?php esc_html_e('Project Feature:', 'edgeexpo'); ?></h4>
<!-- /wp:heading -->

<!-- wp:list {"className":"is-style-ee-checks"} -->
<ul class="wp-block-list is-style-ee-checks"><!-- wp:list-item -->
<li><?php esc_html_e('Custom Exhibition Stand', 'edgeexpo'); ?></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
