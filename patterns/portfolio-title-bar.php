<?php
/**
 * Title: Portfolio Title Bar
 * Slug: edgeexpo/portfolio-title-bar
 * Categories: edgeexpo-sections
 * Keywords: portfolio, title
 * Description: Grey title bar and intro for the Portfolio listing.
 *
 * @package Edge_Expos
 */

echo edgeexpo_title_bar(__('Portfolio', 'edgeexpo')) . edgeexpo_intro(__('Our Work', 'edgeexpo'), array(__('A selection of exhibition stands, point of sale, shop fitting and container projects designed and built by Edge Expos.', 'edgeexpo'))); // phpcs:ignore WordPress.Security.EscapeOutput -- block markup, escaped when built.
