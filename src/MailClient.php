<?php

namespace Tracker;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailClient
{
    private PHPMailer $mailer;

    public function __construct(array $recipients, $subject, $message)
    {
        $this->mailer = new PHPMailer(true);
        $mail = $this->mailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['GMAIL_SMTP_USER'] ?? getenv('GMAIL_SMTP_USER');
        $mail->Password = $_ENV['GMAIL_APP_PASSWORD'] ?? getenv('GMAIL_APP_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('kschmanski1@gmail.com', 'UltraSignup Tracker');
        foreach ($recipients as $recipient) {
            $mail->addAddress($recipient);
        }

        $mail->Subject = $subject;
        $mail->Body = $message;
    }

    public function send()
    {
        try {
            $this->mailer->send();
        } catch (Exception $e) {
            echo "SMTP failed: {$this->mailer->ErrorInfo}\n";
        }
    }
}
