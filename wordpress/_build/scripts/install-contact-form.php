<?php
/**
 * Create/update the general Contact Form and place it on /get-in-touch/.
 *
 * Run from WP-CLI:
 * wp eval-file /var/www/html/_build/scripts/install-contact-form.php --path=/var/www/html
 */

if ( ! class_exists( 'Forminator_API' ) ) {
	wp_die( '[contact-form] ERROR: Forminator is not active.' );
}

$recipient = 'lewishamsportsconsortium@gmail.com';
$form_name = 'Contact Form';

$existing = get_page_by_path( 'contact-form', OBJECT, 'forminator_forms' );
$form_id  = $existing ? (int) $existing->ID : 0;

$wrappers = array(
	array(
		'wrapper_id'   => 'wrapper-contact-full-name',
		'parent_group' => '',
		'fields'       => array(
			array(
				'element_id'       => 'text-1',
				'type'             => 'text',
				'options'          => array(),
				'cols'             => '12',
				'conditions'       => array(),
				'input_type'       => 'line',
				'field_label'      => 'Full Name',
				'required'         => '1',
				'required_message' => 'Full name is required',
			),
		),
	),
	array(
		'wrapper_id'   => 'wrapper-contact-number',
		'parent_group' => '',
		'fields'       => array(
			array(
				'element_id'                  => 'phone-1',
				'type'                        => 'phone',
				'options'                     => array(),
				'cols'                        => '12',
				'conditions'                  => array(),
				'field_label'                 => 'Contact Number',
				'validation'                  => 'international',
				'phone_international_country' => 'gb',
				'required'                    => '',
			),
		),
	),
	array(
		'wrapper_id'   => 'wrapper-contact-email',
		'parent_group' => '',
		'fields'       => array(
			array(
				'element_id'       => 'email-1',
				'type'             => 'email',
				'options'          => array(),
				'cols'             => '12',
				'conditions'       => array(),
				'field_label'      => 'Email Address',
				'required'         => '1',
				'required_message' => 'Email address is required',
			),
		),
	),
	array(
		'wrapper_id'   => 'wrapper-contact-postal-address',
		'parent_group' => '',
		'fields'       => array(
			array(
				'element_id'  => 'textarea-1',
				'type'        => 'textarea',
				'options'     => array(),
				'cols'        => '12',
				'conditions'  => array(),
				'input_type'  => 'line',
				'field_label' => 'Postal Address',
				'required'    => '',
			),
		),
	),
	array(
		'wrapper_id'   => 'wrapper-contact-message',
		'parent_group' => '',
		'fields'       => array(
			array(
				'element_id'       => 'textarea-2',
				'type'             => 'textarea',
				'options'          => array(),
				'cols'             => '12',
				'conditions'       => array(),
				'input_type'       => 'paragraph',
				'field_label'      => 'Your Message',
				'required'         => '1',
				'required_message' => 'Message is required',
			),
		),
	),
);

$settings = array(
	'formName'                    => $form_name,
	'version'                     => defined( 'FORMINATOR_VERSION' ) ? FORMINATOR_VERSION : '1.55.0',
	'form-border-style'           => 'none',
	'fields-style'                => 'open',
	'validation'                  => 'on_submit',
	'akismet-protection'          => '',
	'form-style'                  => 'default',
	'form-substyle'               => 'default',
	'enable-ajax'                 => 'true',
	'autoclose'                   => 'true',
	'submission-indicator'        => 'show',
	'indicator-label'             => 'Submitting...',
	'cform-color-option'          => 'theme',
	'store_submissions'           => '1',
	'description-position'        => 'above',
	'form-type'                   => 'default',
	'submission-behaviour'        => 'behaviour-thankyou',
	'thankyou-message'            => 'Thanks! We’ve received your message and will get back to you shortly.',
	'submitData'                  => array(
		'custom-submit-text'         => 'Send',
		'custom-invalid-form-message' => 'Error: Your form is not valid, please fix the errors!',
	),
	'validation-inline'           => '1',
	'form-expire'                 => 'no_expire',
	'form-padding-top'            => '0',
	'form-padding-right'          => '0',
	'form-padding-bottom'         => '0',
	'form-padding-left'           => '0',
	'form-border-width'           => '0',
	'form-border-radius'          => '0',
	'cform-label-font-size'       => '14',
	'cform-label-font-weight'     => 'bold',
	'cform-input-font-size'       => '16',
	'cform-button-font-size'      => '14',
	'cform-button-font-weight'    => '500',
	'payment_require_ssl'         => '',
	'submission-file'             => 'delete',
	'trigger_from'                => 'builder',
	'template_type'               => 'preset',
	'template_name'               => 'Blank Form',
	'form_name'                   => 'contact-form',
	'form_status'                 => 'publish',
	'notification_count'          => 1,
	'previous_status'             => 'publish',
);

$notifications = array(
	array(
		'slug'             => 'notification-admin-contact',
		'label'            => 'Admin Email',
		'email-recipients' => 'default',
		'recipients'       => $recipient,
		'email-subject'    => 'New Contact Form Entry #{submission_id}',
		'email-editor'     => 'You have a new website contact form submission: <br /> {all_fields} <br />---<br /> This message was sent from {site_url}.',
		'email-attachment' => 'true',
		'type'             => 'default',
		'conditions'       => array(),
		'routing'          => array(),
	),
);

if ( $form_id <= 0 ) {
	$form_id = Forminator_API::add_form( $form_name, $wrappers, $settings, Forminator_Form_Model::STATUS_PUBLISH );
}

if ( is_wp_error( $form_id ) ) {
	wp_die( '[contact-form] ERROR: ' . $form_id->get_error_message() );
}

$updated_form_id = Forminator_API::update_form(
	(int) $form_id,
	$wrappers,
	$settings,
	Forminator_Form_Model::STATUS_PUBLISH,
	$notifications
);

if ( is_wp_error( $updated_form_id ) ) {
	wp_die( '[contact-form] ERROR: ' . $updated_form_id->get_error_message() );
}

$form_id = (int) $updated_form_id;
update_option( 'forminator_sender_email_address', 'noreply@lsportsc.org' );

$page = get_page_by_path( 'get-in-touch', OBJECT, 'page' );
if ( ! $page ) {
	wp_die( '[contact-form] ERROR: /get-in-touch/ page not found.' );
}

$section = <<<HTML

<!-- wp:group {"align":"full","className":"lsc-contact-form-section","backgroundColor":"smoke","style":{"spacing":{"padding":{"top":"3.5rem","right":"var:preset|spacing|40","bottom":"4rem","left":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull lsc-contact-form-section has-smoke-background-color has-background" style="padding-top:3.5rem;padding-right:var(--wp--preset--spacing--40);padding-bottom:4rem;padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Send us a message</h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"lsc-contact-form","layout":{"type":"constrained","contentSize":"820px"}} -->
<div class="wp-block-group lsc-contact-form"><!-- wp:shortcode -->
[forminator_form id="$form_id" title="Contact Form"]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
HTML;

$content = $page->post_content;
$pattern = '/\n?<!-- wp:group \{"align":"full","className":"lsc-contact-form-section".*?<!-- \/wp:group -->\s*$/s';

if ( preg_match( $pattern, $content ) ) {
	$content = preg_replace( $pattern, $section, $content, 1 );
} elseif ( false === strpos( $content, '[forminator_form id="' . $form_id . '" title="Contact Form"]' ) ) {
	$content .= $section;
}

$result = wp_update_post(
	array(
		'ID'           => (int) $page->ID,
		'post_content' => $content,
	),
	true
);

if ( is_wp_error( $result ) ) {
	wp_die( '[contact-form] ERROR: ' . $result->get_error_message() );
}

WP_CLI::success( 'Contact Form ' . $form_id . ' installed on /get-in-touch/.' );
