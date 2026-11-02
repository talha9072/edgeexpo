<?php
/**
 * Title: Home Intro
 * Slug: edgeexpo/home-intro
 * Categories: edgeexpo-sections
 * Keywords: intro, exhibition stands
 * Description: EXHIBITION STANDS heading, intro text, separator and the three-principles line.
 *
 * @package Edge_Expos
 */

$c = edgeexpo_page_content('home');
echo edgeexpo_intro($c['heading'], $c['leads'], $c['subhead'], 1); // phpcs:ignore WordPress.Security.EscapeOutput -- block markup, escaped when built.
