<?php
/**
 * Title: Thank You
 * Slug: edgeexpo/status-thank-you
 * Categories: edgeexpo-sections
 * Keywords: thank you
 * Description: Message shown after the contact form is sent.
 *
 * @package Edge_Expos
 */

echo edgeexpo_title_bar(__('Thank You', 'edgeexpo')) . edgeexpo_intro(__('Thank you!', 'edgeexpo'), array(__('Your message has been sent. A member of the Edge Expos team will be in touch shortly.', 'edgeexpo'))) . edgeexpo_button_row(__('View our portfolio', 'edgeexpo'), home_url('/portfolio/')); // phpcs:ignore WordPress.Security.EscapeOutput -- block markup, escaped when built.
