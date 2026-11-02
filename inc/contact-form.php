<?php
/**
 * Contact form: [edgeexpo_contact_form] shortcode and its admin-post handler.
 *
 * Same fields as the old Contact Form 7 form (Name, Email, Company, Message). Submissions are
 * validated, checked against a nonce and a honeypot, emailed to Cara and Kim (filterable with
 * edgeexpo_contact_recipients), then the visitor is sent to the Thank You page.
 *
 * @package Edge_Expos
 */

if (! defined('ABSPATH')) {
    exit;
}

const EDGEEXPO_CONTACT_ACTION = 'edgeexpo_contact';

/**
 * Render the form. Errors come back as ?contact=<code> after a failed submission.
 */
function edgeexpo_contact_form_shortcode(): string
{
    $messages = array(
        'invalid' => __('Please enter your name, a valid email address and a message.', 'edgeexpo'),
        'failed'  => __('Sorry, your message could not be sent. Please try again or email us directly.', 'edgeexpo'),
    );
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only status flag.
    $status = isset($_GET['contact']) ? sanitize_key(wp_unslash($_GET['contact'])) : '';

    $fields = array(
        array('name', __('Your Name (required)', 'edgeexpo'), 'text', 'name', true),
        array('email', __('Your Email (required)', 'edgeexpo'), 'email', 'email', true),
        array('company', __('Your Company', 'edgeexpo'), 'text', 'organization', false),
    );

    ob_start();
    ?>
    <form class="ee-form" id="contact-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php if (isset($messages[$status])) : ?>
            <p class="ee-form__notice" role="alert"><?php echo esc_html($messages[$status]); ?></p>
        <?php endif; ?>
        <input type="hidden" name="action" value="<?php echo esc_attr(EDGEEXPO_CONTACT_ACTION); ?>">
        <input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink()); ?>">
        <?php wp_nonce_field(EDGEEXPO_CONTACT_ACTION, 'ee_contact_nonce'); ?>
        <div class="ee-form__hp" aria-hidden="true">
            <label for="ee-website"><?php esc_html_e('Leave this field empty', 'edgeexpo'); ?></label>
            <input type="text" id="ee-website" name="website" tabindex="-1" autocomplete="off">
        </div>
        <?php foreach ($fields as [$name, $label, $type, $autocomplete, $required]) : ?>
            <p class="ee-form__field">
                <label for="ee-<?php echo esc_attr($name); ?>"><?php echo esc_html($label); ?></label>
                <input type="<?php echo esc_attr($type); ?>" id="ee-<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($name); ?>" autocomplete="<?php echo esc_attr($autocomplete); ?>" <?php echo $required ? 'required' : ''; ?>>
            </p>
        <?php endforeach; ?>
        <p class="ee-form__field">
            <label for="ee-message"><?php esc_html_e('Your Message', 'edgeexpo'); ?></label>
            <textarea id="ee-message" name="message" rows="7" required></textarea>
        </p>
        <p class="ee-form__actions">
            <button type="submit" class="wp-element-button"><?php esc_html_e('Send', 'edgeexpo'); ?></button>
        </p>
    </form>
    <?php
    return (string) ob_get_clean();
}
add_shortcode('edgeexpo_contact_form', 'edgeexpo_contact_form_shortcode');

/**
 * Handle a submission from logged-in and anonymous visitors.
 */
function edgeexpo_handle_contact_form(): void
{
    $back = isset($_POST['redirect_to']) ? esc_url_raw(wp_unslash($_POST['redirect_to'])) : home_url('/contact-us/');
    $back = wp_validate_redirect($back, home_url('/contact-us/'));

    $nonce = isset($_POST['ee_contact_nonce']) ? sanitize_text_field(wp_unslash($_POST['ee_contact_nonce'])) : '';
    if (! wp_verify_nonce($nonce, EDGEEXPO_CONTACT_ACTION)) {
        wp_safe_redirect(add_query_arg('contact', 'failed', $back) . '#contact-form');
        exit;
    }

    // Honeypot filled in: pretend success so bots learn nothing.
    if (! empty($_POST['website'])) {
        wp_safe_redirect(home_url('/thank-you/'));
        exit;
    }

    $data = array(
        'name'    => sanitize_text_field(wp_unslash($_POST['name'] ?? '')),
        'email'   => sanitize_email(wp_unslash($_POST['email'] ?? '')),
        'company' => sanitize_text_field(wp_unslash($_POST['company'] ?? '')),
        'message' => sanitize_textarea_field(wp_unslash($_POST['message'] ?? '')),
    );

    if ('' === $data['name'] || ! is_email($data['email']) || '' === $data['message']) {
        wp_safe_redirect(add_query_arg('contact', 'invalid', $back) . '#contact-form');
        exit;
    }

    $to      = apply_filters('edgeexpo_contact_recipients', edgeexpo_contact()['emails']);
    /* translators: %s: sender's name */
    $subject = sprintf(__('New website enquiry from %s', 'edgeexpo'), $data['name']);
    $body    = sprintf(
        "Name: %s\nEmail: %s\nCompany: %s\n\n%s\n",
        $data['name'],
        $data['email'],
        $data['company'] ?: '-',
        $data['message']
    );
    $headers = array('Reply-To: "' . str_replace(array('"', '\\', '<', '>'), '', $data['name']) . '" <' . $data['email'] . '>');

    if (! wp_mail($to, $subject, $body, $headers)) {
        wp_safe_redirect(add_query_arg('contact', 'failed', $back) . '#contact-form');
        exit;
    }

    wp_safe_redirect(home_url('/thank-you/'));
    exit;
}
add_action('admin_post_' . EDGEEXPO_CONTACT_ACTION, 'edgeexpo_handle_contact_form');
add_action('admin_post_nopriv_' . EDGEEXPO_CONTACT_ACTION, 'edgeexpo_handle_contact_form');
