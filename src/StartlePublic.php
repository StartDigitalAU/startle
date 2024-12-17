<?php

namespace TheStart\Startle;

class StartlePublic
{
	private $slack_webhook_url;

	/**
	 * Get things started
	 *
	 * @return void
	 */
	public function __construct()
	{
		// 1 so it runs before any plugins potentially generate warnings during shutdown after a fatal error.
		add_action('shutdown', [$this, 'shutdown'], 1);

		$settings = get_option('startle_settings', array());
		$this->slack_webhook_url = isset($settings['slack_webhook_url']) ? $settings['slack_webhook_url'] : '';
	}

	/**
	 * Send email notification for the error
	 *
	 * @param array $error Error details
	 * @return bool
	 */
	public function sendEmailNotification($error)
	{
		$settings = get_option('startle_settings', []);
		$emails = isset($settings['notification_emails']) ? $settings['notification_emails'] : '';
		$recipients = array_map('trim', explode(',', $emails));

		if (empty($recipients)) {
			error_log('Startle: No notification emails configured');
			return false;
		}
		$content = ErrorNotificationTemplate::generate($error);

		try {
			if (function_exists('wp_mail')) {
				add_filter('wp_mail_content_type', function () {
					return 'text/html';
				});
				$to = implode(', ', array_unique($recipients));
				$headers = [
					'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
					'Content-Type: text/html; charset=UTF-8'
				];
				wp_mail(
					$to,
					'Startle notification for ' . get_home_url(),
					$content,
					$headers
				);
			} else {
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
				mail(
					$recipients[0],
					'Startle notification for ' . get_home_url(),
					$content,
					$headers
				);
			}
			return true;
		} catch (\Exception $e) {
			error_log('Startle: Failed to send email - ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Catch any fatal errors and act on them
	 *
	 * @param bool $isTest Whether this is a test notification
	 * @return bool|null
	 */
	public function shutdown($isTest = false)
	{
		$error = error_get_last();

		if ($isTest) {
			$error = [
				"type"     => 1, // E_ERROR
				"message"  => "This is a test notification from Fatal Error Notify",
				"file"     => __FILE__,
				"line"     => __LINE__
			];
		}

		if (is_null($error)) {
			return null;
		}


		// Skip certain warnings
		if ($error['type'] === E_WARNING && (
			str_contains($error['message'], 'unlink') ||
			str_contains($error['message'], 'rmdir') ||
			str_contains($error['message'], 'mkdir')
		)) {
			return null;
		}

		$settings = get_option('startle_settings', array());

		// Only check settings if not a test
		if (!$isTest) {
			if (empty($settings) || empty($settings['levels'])) {
				return null;
			}

			if (empty($settings['levels'][$error['type']])) {
				return null;
			}
		}

		// Rate limiting unless it's a test
		if (!$isTest) {
			$hash = md5($error['message']);
			$transient = get_transient('startle_' . $hash);
			if (!empty($transient)) {
				return null;
			}
			set_transient('startle_' . $hash, true, HOUR_IN_SECONDS);
		}

		$this->sendEmailNotification($error);

		if (!empty($settings['slack_webhook_url'])) {
			$this->slack_webhook_url = $settings['slack_webhook_url'];
			$this->sendSlackNotification($error);
		}
	}

	// Keep the existing sendSlackNotification method from the previous implementation
	public function sendSlackNotification($error)
	{
		if (empty($this->slack_webhook_url)) {
			return;
		}

		// Format site name and remove http/https for cleaner display
		$site_url = get_home_url();
		$site_name = str_replace(['http://', 'https://'], '', $site_url);

		$payload = [
			'blocks' => [
				[
					'type' => 'header',
					'text' => [
						'type' => 'plain_text',
						'text' => '💀 Fatal Error 💀',
						'emoji' => true
					]
				],
				[
					'type' => 'section',
					'fields' => [
						[
							'type' => 'mrkdwn',
							'text' => "*Site:*\n" . $site_name
						],
						[
							'type' => 'mrkdwn',
							'text' => "*Time:*\n" . gmdate('Y-m-d H:i:s', time() + 8 * 3600)
						]
					]
				],
				[
					'type' => 'section',
					'fields' => [
						[
							'type' => 'mrkdwn',
							'text' => "*Error Level:*\n" . Startle()->mapErrorCodeToType($error['type'])
						]
					]
				],
				[
					'type' => 'section',
					'text' => [
						'type' => 'mrkdwn',
						'text' => "*Error Message:*\n```" . $error['message'] . "```"
					]
				],
				[
					'type' => 'section',
					'fields' => [
						[
							'type' => 'mrkdwn',
							'text' => "*File:*\n" . basename($error['file'])
						],
						[
							'type' => 'mrkdwn',
							'text' => "*Line:*\n" . $error['line']
						]
					]
				],
				[
					'type' => 'context',
					'elements' => [
						[
							'type' => 'mrkdwn',
							'text' => "🔗 " . $_SERVER['REQUEST_URI']
						]
					]
				],
				[
					'type' => 'divider'
				]
			]
		];

		wp_remote_post($this->slack_webhook_url, [
			'body' => json_encode($payload),
			'headers' => ['Content-Type' => 'application/json'],
			'timeout' => 30
		]);
	}
}

new StartlePublic();
