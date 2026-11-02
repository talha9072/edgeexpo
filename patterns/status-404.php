<?php
/**
 * Title: Page Not Found
 * Slug: edgeexpo/status-404
 * Categories: edgeexpo-sections
 * Keywords: 404, not found
 * Inserter: no
 * Description: 404 message with links back to the home page and portfolio.
 *
 * @package Edge_Expos
 */

echo edgeexpo_title_bar(__('Page not found', 'edgeexpo')) . edgeexpo_intro(__('Sorry, this page could not be found', 'edgeexpo'), array(__('The page may have moved when the site was rebuilt. Try the menu above, or have a look at our recent work.', 'edgeexpo'))) . edgeexpo_portfolio_strip(); // phpcs:ignore WordPress.Security.EscapeOutput -- block markup, escaped when built.
