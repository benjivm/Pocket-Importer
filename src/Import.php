<?php

namespace App;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

class Import extends Command
{
    protected $signature = 'import';

    protected $description = 'Import a Pocket export file';

    /**
     * The HTTP client.
     *
     * @var PendingRequest
     */
    private $client;

    public function __construct()
    {
        parent::__construct();

        $this->client = (new Factory())
            ->baseUrl('https://getpocket.com/v3/')
            ->withQueryParameters([
                'consumer_key' => $_ENV['POCKET_CONSUMER_KEY'],
                'access_token' => $_ENV['POCKET_ACCESS_TOKEN'],
            ]);
    }

    public function handle(): void
    {
        $html = file_get_contents('ril_export.html');

        $crawler = new Crawler($html);

        $items = new Collection();

        $crawler
            ->filter('a')
            ->each(fn (Crawler $node) => $items->push([
                'title' => $node->text(),
                'url' => $node->attr('href'),
                'time_added' => $node->attr('time_added'),
                'tags' => $node->attr('tags'),
            ]));

        if ($this->confirm('Import '.$items->count().' items?')) {
            $items->each(function ($item) {
                $response = $this->client->post('add', [
                    'url' => $item['url'],
                    'time' => $item['time_added'],
                    'tags' => $item['tags'],
                ]);

                if ($response->failed()) {
                    $this->warn("Failed to import {$item['title']}: got status {$response->status()}");
                }

                sleep(1);
            });
        }
    }
}
