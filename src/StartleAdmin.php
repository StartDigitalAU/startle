<?php

namespace TheStart\Startle;

class StartleAdmin
{

	/**
	 * Get things started
	 *
	 * @since 1.0
	 * @return void
	 */

	public function __construct()
	{
		add_action('admin_menu', [$this, 'createAdminMenu']);
		add_action('wp_ajax_test_error', [$this, 'testError']);
		add_action('admin_init', [$this, 'addSlackWebhookSetting']);
		add_action('wp_ajax_test_slack_notification', [$this, 'handleTestSlackNotification']);
	}

	/**
	 * Register admin settings menu
	 *
	 * @since 1.0
	 * @return void
	 */

	public function createAdminMenu()
	{
		$id = add_options_page(
			'Startle Settings',
			'Startle',
			'manage_options',
			'startle',
			[$this, 'settings_page']
		);

		add_action('load-' . $id, [$this, 'enqueueScripts']);
	}

	/**
	 * Register CSS files
	 *
	 * @since 1.0
	 * @return void
	 */

	public function enqueueScripts()
	{
		remove_all_actions('admin_notices'); // We don't need to see these on our page.

		wp_enqueue_style('startle', get_template_directory_uri() . '/vendor/thestart/startle/src/assets/admin.css', [], STARTLE_VERSION);
		wp_enqueue_script('testError', get_template_directory_uri() . '/vendor/thestart/startle/src/assets/admin.js', ['jquery'], STARTLE_VERSION);
	}

	/**
	 * Handles AJAX request to test error notification.
	 *
	 * @access public
	 * @return void
	 */

	public function testError()
	{
		if (!check_ajax_referer('startle_settings', 'startle_settings_nonce', false)) {
			wp_send_json_error('Invalid nonce');
			return;
		}

		$error = [
			"type"     => 1, // E_ERROR
			"message"  => "This is a test error from Startle 💀",
			"file"     => __FILE__,
			"line"     => __LINE__
		];
		$public = new StartlePublic();
		$public->sendEmailNotification($error);

		wp_send_json_success(array('message' => 'Test error sent successfully'));
	}

	/**
	 * Send a test slack notification to the configured webhook URL
	 *
	 * @return void
	 */
	function handleTestSlackNotification()
	{
		if (!check_ajax_referer('startle_settings', 'startle_settings_nonce', false)) {
			wp_send_json_error('Invalid nonce');
			return;
		}

		$settings = get_option('startle_settings', []);
		if (empty($settings['slack_webhook_url'])) {
			wp_send_json_error('Slack webhook URL not configured');
			exit;
		}

		$error = [
			"type"     => 1, // E_ERROR
			"message"  => "This is a test notification from Startle 💀",
			"file"     => __FILE__,
			"line"     => __LINE__
		];
		$public = new StartlePublic();
		$public->sendSlackNotification($error);

		wp_send_json_success(array('message' => 'Test notification sent successfully'));
		exit;
	}

	public function addSlackWebhookSetting()
	{
		add_settings_field(
			'slack_webhook_url',
			'Slack Webhook URL',
			'render_slack_webhook_field',
			'startle_settings',
			'startle_settings_section'
		);
	}

	/**
	 * Renders Settings page
	 *
	 * @access public
	 * @return mixed
	 */

	public function settings_page()
	{
		if (isset($_POST['startle_settings_nonce']) && wp_verify_nonce($_POST['startle_settings_nonce'], 'startle_settings')) {
			update_option('startle_settings', map_deep(wp_unslash($_POST['startle_settings']), 'sanitize_text_field'));
			echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
		}

		$settings = get_option('startle_settings', array());
		$webhook_url = isset($settings['slack_webhook_url']) ? $settings['slack_webhook_url'] : '';

		if (empty($settings)) {

			$settings = [
				'levels' => [],
			];

			foreach (Startle()->error_levels as $level_id) {
				// Enable fatal error and parse error by default
				if ($level_id == 1 || $level_id == 4) {
					$settings['levels'][$level_id] = true;
				} else {
					$settings['levels'][$level_id] = false;
				}
			}
		}

?>

		<div class="wrap">
			<h2>Error Notification Settings</h2>

			<form id="startle-settings" method="post">
				<?php wp_nonce_field('startle_settings', 'startle_settings_nonce'); ?>
				<input type="hidden" name="action" value="update">

				<table class="form-table">
					<tr valign="top">
						<th scope="row">Notification Email Addresses</th>
						<td>
							<input type="text"
								name="startle_settings[notification_emails]"
								value="<?php echo esc_attr(isset($settings['notification_emails']) ? $settings['notification_emails'] : ''); ?>"
								class="regular-text">
							<p class="description">Enter email addresses separated by commas (e.g., admin@example.com, dev@example.com)</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">Slack Webhook URL</th>
						<td>
							<input type="password"
								name="startle_settings[slack_webhook_url]"
								value="<?php echo esc_attr($webhook_url); ?>"
								class="regular-text">
							<p class="description">Enter your Slack Incoming Webhook URL</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">Test Email Notification</th>
						<td>
							<button type="button" class="button" data-test-email>Send Test</button>
							<p class="description">Creates a test fatal error to generate an error email.</p>
					</tr>

					<tr valign="top">
						<th scope="row">Test Slack Notification</th>
						<td>
							<button type="button" class="button" data-test-slack>Test Slack Notification</button>
							<p class="description">Creates a test fatal error to generate a Slack notification.</p>
						</td>

					<tr valign="top">
						<th scope="row">Error Levels To Notify</th>
						<td>
							<fieldset class="error-levels">

								<?php foreach (Startle()->error_levels as $i => $level_id) : ?>

									<?php $level_string = Startle()->mapErrorCodeToType($level_id); ?>

									<?php
									if (empty($settings['levels'][$level_id])) {
										$settings['levels'][$level_id] = false;
									}
									?>

									<label for="level_<?php echo $level_string; ?>">
										<input type="checkbox" name="startle_settings[levels][<?php echo $level_id; ?>]" id="level_<?php echo $level_string; ?>" value="1" <?php checked($settings['levels'][$level_id]); ?> />
										<?php echo esc_html($level_string); ?>
									</label>

									<?php
									switch ($level_string) {
										case 'E_ERROR':
											echo '<span class="description"><strong>Recommended:</strong> A fatal run-time error that can\'t be recovered from.</span>';
											break;

										case 'E_WARNING':
											echo '<span class="description">Warnings indicate that something unexpected happened, but the site didn\'t crash.</span>';
											break;

										case 'E_PARSE':
											echo '<span class="description"><strong>Recommended:</strong> A Parse error should catch things like syntax errors.</span>';
											break;

										case 'E_NOTICE':
											echo '<span class="description">Many plugins generate Notice-level errors, and these can usually be ignored.</span>';
											break;

										default:
											break;
									}
									?>

									<br />

								<?php endforeach; ?>
							</fieldset>

						</td>

				</table>

				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="Save Changes" />
				</p>

			</form>

		</div>

<?php
	}
}
