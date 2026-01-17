<?php

namespace Tracker;

use GuzzleHttp\Client;

class WaitlistScraper
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'UltraSignup Waitlist Tracker (personal use)'
            ]
        ]);
    }

    public function fetch(string $url): string
    {
        $response = $this->client->get($url);
        return (string) $response->getBody();
    }
}
