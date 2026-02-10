<?php
require __DIR__ . '/../vendor/autoload.php';


use Dotenv\Dotenv;

$root = dirname(__DIR__);

if (file_exists($root . '/.env')) {
    Dotenv::createImmutable($root)->load();
}
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/cron.log');

error_log('Cron run started at ' . date('c'));

require __DIR__ . '/../vendor/autoload.php';

use Tracker\WaitlistScraper;
use Tracker\WaitlistParser;

$options = getopt('', [
    'url:',
    'name:',
    'debug'
]);

$url = $options['url'] ?? null;
$targetName = $options['name'] ?? null;
$debug = array_key_exists('debug', $options);

if (!$url || !$targetName) {
    echo "Usage: php script.php --url=URL --name=NAME [--debug]\n";
    exit(1);
}

if ($debug) {
    echo "Running in DEBUG mode\n";
}
// $url = 'https://ultrasignup.com/event_waitlist.aspx?did=126417';
// $targetName = 'Kaz Schmanski';

$scraper = new WaitlistScraper();
$parser = new WaitlistParser();

$html = $scraper->fetch($url);
$position = $parser->findPosition($html, $targetName);

error_log("Fetched waitlist position: $position");

$message = $position === null
    ? "Name not found\n"
    : "Current waitlist position for $targetName: {$position}\n";

echo $message;

$headers = [
    'From: UltraSignup Tracker <kschmanski1@gmail.com>',
    'Content-Type: text/plain; charset=UTF-8',
];

$mailto = 'kschmanski1@gmail.com';
$subject = 'UltraSignup Tracker for Elm Creek Backyard Ultra';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!$debug) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['GMAIL_SMTP_USER'] ?? getenv('GMAIL_SMTP_USER');
        $mail->Password = $_ENV['GMAIL_APP_PASSWORD'] ?? getenv('GMAIL_APP_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('kschmanski1@gmail.com', 'UltraSignup Tracker');
        $mail->addAddress('kschmanski1@gmail.com');

        $mail->Subject = 'UltraSignup Tracker: Elm Creek Backyard Ultra';
        $mail->Body = $message;

        $mail->addAddress('katka.svensson@gmail.com');
        $mail->send();
        echo "SMTP email sent\n";
    } catch (Exception $e) {
        echo "SMTP failed: {$mail->ErrorInfo}\n";
    }
}
