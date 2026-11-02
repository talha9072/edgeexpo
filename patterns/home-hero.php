<?php
/**
 * Title: Home Hero
 * Slug: edgeexpo/home-hero
 * Categories: edgeexpo-sections
 * Keywords: hero, slider, banner
 * Description: Full-width banner under the menu (the 2025 slider images were not archived, so this uses a mosaic of project photos — replace the image in the editor).
 *
 * @package Edge_Expos
 */

echo edgeexpo_block_cover('ee-hero', get_theme_file_uri('assets/img/home-hero.webp'), edgeexpo_block_h(2, 'ee-hero__title', 'Exhibition Stands') . edgeexpo_block_p('ee-hero__tag', 'From concept to completion'), 340); // phpcs:ignore WordPress.Security.EscapeOutput -- block markup, escaped when built.
