<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailHelper
{
    /**
     * Send email using Laravel Mail
     *
     * @param string|array $to Email address(es)
     * @param string $subject Email subject
     * @param string $view Blade view name or HTML content
     * @param array $data Data to pass to the view
     * @param string|null $fromEmail From email address
     * @param string|null $fromName From name
     * @param array|null $attachments Array of file paths to attach
     * @return bool
     */
    public static function send(
        string|array $to,
        string $subject,
        string $view,
        array $data = [],
        ?string $fromEmail = null,
        ?string $fromName = null,
        ?array $attachments = null
    ): bool {
        try {
            $fromEmail = $fromEmail ?? config('mail.from.address');
            $fromName = $fromName ?? config('mail.from.name');

            // If view doesn't exist, treat it as raw HTML
            $isRawHtml = !view()->exists($view);

            Mail::send($isRawHtml ? [] : $view, $data, function ($message) use ($to, $subject, $fromEmail, $fromName, $view, $isRawHtml, $data, $attachments) {
                $message->from($fromEmail, $fromName)
                    ->subject($subject);

                // Handle multiple recipients
                if (is_array($to)) {
                    foreach ($to as $email) {
                        $message->to($email);
                    }
                } else {
                    $message->to($to);
                }

                // If raw HTML, set body directly
                if ($isRawHtml) {
                    $message->html($view);
                }

                // Attach files if provided
                if ($attachments) {
                    foreach ($attachments as $attachment) {
                        if (file_exists($attachment)) {
                            $message->attach($attachment);
                        }
                    }
                }
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Email sending failed', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send email with HTML content directly
     *
     * @param string|array $to
     * @param string $subject
     * @param string $htmlContent
     * @param string|null $fromEmail
     * @param string|null $fromName
     * @return bool
     */
    public static function sendHtml(
        string|array $to,
        string $subject,
        string $htmlContent,
        ?string $fromEmail = null,
        ?string $fromName = null
    ): bool {
        return self::send($to, $subject, $htmlContent, [], $fromEmail, $fromName);
    }

    /**
     * Send password reset email
     *
     * @param string $to
     * @param string $resetToken
     * @param string $resetUrl
     * @return bool
     */
    public static function sendPasswordReset(string $to, string $resetToken, string $resetUrl): bool
    {
        $subject = 'Password Reset Request';
        $data = [
            'resetUrl' => $resetUrl,
            'resetToken' => $resetToken,
            'expiresIn' => config('auth.passwords.users.expire', 60) . ' minutes',
        ];

        $view = 'emails.password-reset';
        
        // If view doesn't exist, use default HTML
        if (!view()->exists($view)) {
            $html = self::getDefaultPasswordResetTemplate($resetUrl, $resetToken);
            return self::sendHtml($to, $subject, $html);
        }

        return self::send($to, $subject, $view, $data);
    }

    /**
     * Send passcode email
     *
     * @param string $to
     * @param string $passcode
     * @return bool
     */
    public static function sendPasscode(string $to, string $passcode): bool
    {
        $subject = 'Your Passcode';
        $data = [
            'passcode' => $passcode,
        ];

        $view = 'emails.passcode';
        
        // If view doesn't exist, use default HTML
        if (!view()->exists($view)) {
            $html = self::getDefaultPasscodeTemplate($passcode);
            return self::sendHtml($to, $subject, $html);
        }

        return self::send($to, $subject, $view, $data);
    }

    /**
     * Get default password reset email template
     *
     * @param string $resetUrl
     * @param string $resetToken
     * @return string
     */
    private static function getDefaultPasswordResetTemplate(string $resetUrl, string $resetToken): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Password Reset</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Password Reset Request</h2>
                <p>You requested to reset your password. Click the link below to reset it:</p>
                <p style='margin: 20px 0;'>
                    <a href='{$resetUrl}' style='background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
                </p>
                <p>Or copy and paste this URL into your browser:</p>
                <p style='word-break: break-all; color: #7f8c8d;'>{$resetUrl}</p>
                <p style='color: #7f8c8d; font-size: 12px;'>This link will expire in " . config('auth.passwords.users.expire', 60) . " minutes.</p>
                <p style='color: #7f8c8d; font-size: 12px;'>If you did not request a password reset, please ignore this email.</p>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get default passcode email template
     *
     * @param string $passcode
     * @return string
     */
    private static function getDefaultPasscodeTemplate(string $passcode): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Your Passcode</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Your Passcode</h2>
                <p>Your passcode has been set. Use this code to verify your identity:</p>
                <div style='background-color: #ecf0f1; padding: 20px; text-align: center; margin: 20px 0; border-radius: 5px;'>
                    <h1 style='color: #3498db; font-size: 32px; letter-spacing: 5px; margin: 0;'>{$passcode}</h1>
                </div>
                <p style='color: #7f8c8d; font-size: 12px;'>Keep this code secure and do not share it with anyone.</p>
            </div>
        </body>
        </html>
        ";
    }
}

