<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleNewsService
{
    public function search(string $query, int $limit = 5, string $lang = 'ru', string $region = 'RU'): array
    {
        try {
            $url = 'https://news.google.com/rss/search?' . http_build_query([
                'q' => $query,
                'hl' => $lang,
                'gl' => $region,
                'ceid' => "{$region}:{$lang}",
            ]);

            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; EncyclopediaBot/1.0)',
                    'Accept' => 'application/rss+xml, application/xml, text/xml',
                ])
                ->get($url);

            if (!$response->ok()) {
                return [];
            }

            return $this->parse($response->body(), $limit);
        } catch (\Throwable $e) {
            Log::warning('GoogleNewsService search failed: ' . $e->getMessage(), ['query' => $query]);
            return [];
        }
    }

    private function parse(string $xml, int $limit): array
    {
        $previous = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($doc === false || !isset($doc->channel->item)) {
            return [];
        }

        $items = [];
        foreach ($doc->channel->item as $item) {
            $title = trim((string) $item->title);
            $link = trim((string) $item->link);
            if ($title === '' || $link === '') {
                continue;
            }

            $sourceName = isset($item->source) ? trim((string) $item->source) : '';
            if ($sourceName === '' && str_contains($title, ' - ')) {
                $parts = explode(' - ', $title);
                $sourceName = trim(array_pop($parts));
                $title = trim(implode(' - ', $parts));
            }

            $pubDate = isset($item->pubDate) ? trim((string) $item->pubDate) : null;
            $description = isset($item->description) ? trim(strip_tags((string) $item->description)) : '';

            $items[] = [
                'title' => $title,
                'link' => $link,
                'source' => $sourceName,
                'published_at' => $pubDate,
                'description' => mb_substr($description, 0, 300),
            ];

            if (count($items) >= $limit) {
                break;
            }
        }

        return $items;
    }
}
