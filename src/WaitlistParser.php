<?php

namespace Tracker;

use Symfony\Component\DomCrawler\Crawler;

class WaitlistParser
{
    public function findPosition(string $html, string $targetName): ?int
    {
        $crawler = new Crawler($html);

        // Target ONLY the waitlist table
        $rows = $crawler->filter('#ContentPlaceHolder1_gvEntrants tr');

        foreach ($rows as $row) {
            $cells = (new Crawler($row))->filter('td');

            if ($cells->count() < 3) {
                continue;
            }

            $order = trim($cells->eq(0)->text());

            // Name is inside <a> within td[2]
            $nameCell = $cells->eq(2);
            $nameLink = $nameCell->filter('a');

            if ($nameLink->count() === 0) {
                continue;
            }

            $name = trim($nameLink->text());

            // Debug (keep for now)
            echo "order='{$order}' name='{$name}'\n";

            if ($this->namesMatch($name, $targetName)) {
                return (int) $order;
            }
        }

        return null;
    }

    private function namesMatch(string $rowName, string $targetName): bool
    {
        $normalize = fn($s) =>
        strtolower(
            preg_replace('/\s+/', ' ', trim($s))
        );

        return str_contains(
            $normalize($rowName),
            $normalize($targetName)
        );
    }
}
