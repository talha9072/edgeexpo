<?php
/**
 * Title: Home Principles
 * Slug: edgeexpo/home-principles
 * Categories: edgeexpo-sections
 * Keywords: principles, boxes, features
 * Description: Three boxes: Excellent Service & Project Management, Passionate About Design, Quality End Product.
 *
 * @package Edge_Expos
 */

echo edgeexpo_boxes(edgeexpo_page_content('home')['boxes']); // phpcs:ignore WordPress.Security.EscapeOutput -- block markup, escaped when built.
