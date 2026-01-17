<?php

require __DIR__ . '/../vendor/autoload.php';

use Tracker\WaitlistScraper;
use Tracker\WaitlistParser;


$url = 'https://ultrasignup.com/event_waitlist.aspx?did=126417';
$targetName = 'Kaz Schmanski';

$scraper = new WaitlistScraper();
$parser = new WaitlistParser();

$html = $scraper->fetch($url);
$position = $parser->findPosition($html, $targetName);

echo $position === null
    ? "Name not found\n"
    : "Current waitlist position: {$position}\n";
