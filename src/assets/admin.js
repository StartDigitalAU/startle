document.addEventListener('DOMContentLoaded', () => {
	const testEmailButton = document.querySelector('[data-test-email]');
	const testSlackButton = document.querySelector('[data-test-slack]');
	const nonce = document.getElementById('startle_settings_nonce').value;

	testEmailButton.addEventListener('click', () => {
		handleButtonState(testEmailButton, 'Sending...', true);

		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'test_error',
				startle_settings_nonce: nonce
			},
			success: (response) => {
				const message = response.success
					? 'Test notification sent successfully!'
					: `Error: ${response.data || 'Failed to send test notification'}`;
				alert(message);
			},
			error: (xhr, status, error) => {
				console.error('AJAX Error:', status, error);
				alert(`Error: ${error}`);
			},
			complete: () => {
				setTimeout(() => {
					handleButtonState(testEmailButton, 'Send Test', false);
				}, 1000);
			}
		});
	});

	testSlackButton.addEventListener('click', () => {
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'test_slack_notification',
				startle_settings_nonce: nonce
			},
			success: (response) => {
				const message = response.success
					? 'Test notification sent successfully!'
					: `Error: ${response.data || 'Failed to send test notification'}`;
				alert(message);
			},
			error: (xhr, status, error) => {
				alert(`Error: ${error}`);
				console.error('AJAX Error:', status, error);
			}
		});
	});
});

function handleButtonState(button, text, disabled) {
	button.textContent = text;
	button.disabled = disabled;
}
