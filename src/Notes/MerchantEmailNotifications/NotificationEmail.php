<?php
/**
 * Handles emailing user notes.
 */

namespace Automattic\WooCommerce\Admin\Notes\MerchantEmailNotifications;

use Automattic\WooCommerce\Admin\Notes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include dependencies.
 */
if ( ! class_exists( 'WC_Email', false ) ) {
	include_once WC_ABSPATH . 'includes/emails/class-wc-email.php';
}

/**
 * NotificationEmail Class.
 */
class NotificationEmail extends \WC_Email {
	/**
	 * Constructor.
	 *
	 * @param Note $note The notification to send.
	 */
	public function __construct( $note ) {
		$this->note          = $note;
		$this->id            = 'merchant_notification';
		$this->template_base = WC_ADMIN_ABSPATH . 'includes/emails/';

		// Call parent constructor.
		parent::__construct();
	}

	/**
	 * This email has no user-facing settings.
	 */
	public function init_form_fields() {}

	/**
	 * This email has no user-facing settings.
	 */
	public function init_settings() {}

	/**
	 * Return template filename.
	 *
	 * @param string $type Type of email to send.
	 * @return string
	 */
	public function get_template_filename( $type = 'html' ) {
		if ( ! in_array( $type, array( 'html', 'plain' ), true ) ) {
			return;
		}
		$content_data      = $this->note->get_content_data();
		$template_filename = "{$type}-merchant-notification.php";
		if ( isset( $content_data->{"template_{$type}"} ) && file_exists( $this->template_base . $content_data->{ "template_{$type}" } ) ) {
			$template_filename = $content_data[ "template_{$type}" ];
		}
		return $template_filename;
	}

	/**
	 * Return email type.
	 *
	 * @return string
	 */
	public function get_email_type() {
		return class_exists( 'DOMDocument' ) ? 'html' : 'plain';
	}

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		$content_data = $this->note->get_content_data();
		if ( isset( $content_data->heading ) ) {
			return $content_data->heading;
		}

		return $this->note->get_title();
	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return $this->note->get_title();
	}

	/**
	 * Get note content.
	 *
	 * @return string
	 */
	public function get_note_content() {
		return $this->note->get_content();
	}

	/**
	 * Get note image.
	 *
	 * @return string
	 */
	public function get_image() {
		return $this->note->get_image();
	}

	/**
	 * Get email action.
	 *
	 * @return stdClass
	 */
	public function get_actions() {
		return $this->note->get_actions();
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->get_template_filename( 'html' ),
			array(
				'email_actions'           => $this->get_actions(),
				'email_content'           => $this->get_note_content(),
				'email_heading'           => $this->get_heading(),
				'email_image'             => $this->get_image(),
				'sent_to_admin'           => true,
				'plain_text'              => false,
				'email'                   => $this,
				'opened_tracking_url'     => $this->opened_tracking_url,
				'trigger_note_action_url' => $this->trigger_note_action_url,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->get_template_filename( 'plain' ),
			array(
				'email_heading'           => $this->get_heading(),
				'email_content'           => $this->get_note_content(),
				'email_actions'           => $this->get_actions(),
				'sent_to_admin'           => true,
				'plain_text'              => true,
				'email'                   => $this,
				'trigger_note_action_url' => $this->trigger_note_action_url,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param string $email Email to send the note.
	 * @param int    $user_id User id to to track the note.
	 */
	public function trigger( $email, $user_id ) {
		$this->recipient               = $email;
		$this->opened_tracking_url     = sprintf(
			'%1$s/wp-json/wc-analytics/admin/notes/tracker/%2$d/user/%3$d',
			site_url(),
			$this->note->get_id(),
			$user_id
		);
		$this->trigger_note_action_url = sprintf(
			'%1$s&external_redirect=1&note=%2$d&user=%3$d&action=',
			wc_admin_url(),
			$this->note->get_id(),
			$user_id
		);
		$this->send(
			$this->get_recipient(),
			$this->get_subject(),
			$this->get_content(),
			$this->get_headers(),
			$this->get_attachments()
		);
		wp_set_current_user( $user_id );
		wc_admin_record_tracks_event( 'wcadmin_email_note_sent', array( 'note_name' => $this->note->get_name() ) );
		wp_set_current_user( 0 );
	}
}
