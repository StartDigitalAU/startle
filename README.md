# Startle: Your WordPress Error Guardian 🚨

**Startle** – because downtime doesn't knock first. Stay cool while we stay on guard.

## Overview

Startle is a lightweight WordPress plugin that acts as your site's vigilant sentinel. When critical errors threaten your website's stability, Startle instantly alerts you through Slack or email, ensuring you're never caught off guard by unexpected issues.

## Features

🔍 **Comprehensive Error Monitoring**
  - Catch fatal errors, parse errors, and critical warnings
  - Customizable error level notifications

📧 **Instant Notifications**
  - Email alerts to your team
  - Slack integration for real-time updates

🎛️ **Flexible Configuration**
  - Choose which error levels to monitor
  - Multiple notification email addresses
  - Easy Slack webhook setup

## Installation

Install via Composer:

```bash
composer require thestart/startle
```

## Configuration

1. Install via Composer
2. Navigate to Settings > Startle
3. Configure your notification preferences:
   - Add email addresses for notifications
   - Set up a Slack webhook URL
   - Select error levels to monitor

## Quick Test

Use the built-in test buttons to:
- Send a test email notification
- Send a test Slack notification

## Error Levels Supported

- **E_ERROR**: Fatal run-time errors (recommended)
- **E_WARNING**: Unexpected but non-fatal warnings
- **E_PARSE**: Syntax errors
- **E_NOTICE**: Minor issues (usually ignorable)

## Why Startle?

- 🐶 Like a digital watchdog: Silent when everything's fine, loud when it matters
- ⚡ Instant notifications
- 🛡️ Proactive error tracking
- 🔬 Detailed error context

## Requirements

- WordPress 6.0+
- PHP 8.0+
- Composer

## Contributing

Contributions are welcome! Please submit pull requests or open issues on our GitHub repository.

## License

[MIT License](LICENSE)

---

**Startle** – Keeping your WordPress site healthy, one alert at a time. 💪🌐
