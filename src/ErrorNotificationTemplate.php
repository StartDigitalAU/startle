<?php

namespace TheStart\Startle;

class ErrorNotificationTemplate
{
    /**
     * Generate a clean, modern HTML email template for error notifications
     *
     * @param array $error Error details
     * @return string HTML-formatted email content
     */
    public static function generate($error)
    {
        // Sanitize and prepare error details
        $errorLevel = esc_html(Startle()->mapErrorCodeToType($error['type']));
        $errorMessage = esc_html($error['message']);
        $errorFile = esc_html(basename($error['file']));
        $errorLine = esc_html($error['line']);
        $siteUrl = get_home_url();
        $siteName = esc_html(str_replace(['http://', 'https://'], '', $siteUrl));

        // Get additional context
        $requestUri = esc_html($_SERVER['REQUEST_URI']);
        $referrer = isset($_SERVER['HTTP_REFERER']) ? esc_html($_SERVER['HTTP_REFERER']) : 'Unknown';
        $userId = get_current_user_id();
        $currentUser = $userId ? get_userdata($userId) : null;

        // Construct the HTML email
        $emailContent = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .error-container {
                    background-color: #f8d7da;
                    border: 1px solid #f5c6cb;
                    border-radius: 4px;
                    padding: 15px;
                    margin-bottom: 20px;
                }
                .error-details {
                    background-color: #fff;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    padding: 15px;
                }
                h1 {
                    color: #721c24;
                    border-bottom: 2px solid #f5c6cb;
                    padding-bottom: 10px;
                }
                .detail-label {
                    font-weight: bold;
                    color: #495057;
                }
                .site-info {
                    margin-top: 20px;
                    font-size: 0.9em;
                    color: #6c757d;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <h1>Fatal Error Notification</h1>

            <div class="error-container">
                <div class="error-details">
                    <p><span class="detail-label">Error Level:</span> ' . $errorLevel . '</p>
                    <p><span class="detail-label">Message:</span> ' . $errorMessage . '</p>
                    <p><span class="detail-label">Location:</span> ' . $errorFile . ' (Line ' . $errorLine . ')</p>
                </div>
            </div>

            <div class="error-details">
                <p><span class="detail-label">Request URI:</span> ' . $requestUri . '</p>
                <p><span class="detail-label">Referrer:</span> ' . $referrer . '</p>';

        // Add user details if available
        if ($currentUser) {
            $emailContent .= '
                <p><span class="detail-label">User:</span> ' . esc_html($currentUser->display_name) . ' (ID: ' . $userId . ')</p>
                <p><span class="detail-label">User Email:</span> ' . esc_html($currentUser->user_email) . '</p>';
        }

        $emailContent .= '
            </div>

            <div class="site-info">
                <p>Sent from: <a href="' . esc_url($siteUrl) . '">' . $siteName . '</a><br>
                ' . gmdate('Y-m-d H:i:s T') . '</p>
            </div>
        </body>
        </html>';

        return $emailContent;
    }
}
