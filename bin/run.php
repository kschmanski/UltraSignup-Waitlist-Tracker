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
use Tracker\MailClient;

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

$scraper = new WaitlistScraper();
$parser = new WaitlistParser();

$html = $scraper->fetch($url);
$position = $parser->findPosition($html, $targetName);
$eventName = $parser->parseEventName($html);

echo $eventName . "\n";

$message = $position === null
    ? "Name not found\n"
    : "Current waitlist position for $targetName: {$position}\n";

echo $message;

if (!$debug) {
    $recipients = [
        'kschmanski1@gmail.com',
        'katka.svensson@gmail.com',
    ];
    $subject = "UltraSignup Tracker for $eventName";

    $mail = new MailClient($recipients, $subject, $message);

    $mail->send();
    echo "SMTP email sent\n";
}
