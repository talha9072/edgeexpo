<?php
/**
 * Title: Contact Us Page
 * Slug: edgeexpo/contact-page
 * Categories: edgeexpo-sections
 * Keywords: contact, form
 * Description: Title bar, intro, contact details and the enquiry form.
 *
 * @package Edge_Expos
 */

$edgeexpo_c = edgeexpo_contact();
$edgeexpo_details = edgeexpo_block_h(3, 'ee-contact__label', __('Call us', 'edgeexpo'))
    . edgeexpo_block_p('ee-contact__value', implode('<br>', array_map('esc_html', $edgeexpo_c['phones'])))
    . edgeexpo_block_h(3, 'ee-contact__label', __('Email', 'edgeexpo'))
    . edgeexpo_block_p('ee-contact__value', implode('<br>', array_map(static fn ($e) => '<a href="mailto:' . esc_attr($e) . '">' . esc_html($e) . '</a>', $edgeexpo_c['emails'])))
    . edgeexpo_block_h(3, 'ee-contact__label', __('Workshops', 'edgeexpo'))
    . edgeexpo_block_p('ee-contact__value', esc_html($edgeexpo_c['workshops']));
$edgeexpo_form = "<!-- wp:shortcode -->\n[edgeexpo_contact_form]\n<!-- /wp:shortcode -->";

echo edgeexpo_title_bar(__('Contact Us', 'edgeexpo'))
    . edgeexpo_intro(
        __('Get in touch!', 'edgeexpo'),
        array('The Edge team has several years of experience within the Exhibition Industry, which not only gives us the expertise to deliver cutting edge designs, but also the ability to offer a competitive solution to our clients – making certain they get the most on their Return on Investment (ROI). With partner workshops in Johannesburg, Cape Town and Durban, we have the ability to deliver a cost effective product nationwide.')
    )
    . edgeexpo_block_section('ee-contact', edgeexpo_block_group('ee-contact__grid', edgeexpo_block_group('ee-contact__details', $edgeexpo_details) . edgeexpo_block_group('ee-contact__form', $edgeexpo_form)), array('anchor' => 'contact')); // phpcs:ignore WordPress.Security.EscapeOutput -- block markup, escaped when built.
