<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WikipediaService
{
    public function parseUrl(string $url): ?array
    {
        if (preg_match('#https?://([a-z]{2,3})\.wikipedia\.org/wiki/(.+)#i', $url, $m)) {
            return [
                'lang' => $m[1],
                'title' => urldecode($m[2]),
            ];
        }

        return null;
    }

    public function fetch(string $lang, string $title): ?array
    {
        $userAgent = sprintf(
            '%s/1.0 (%s; contact: %s) Laravel-HttpClient',
            config('app.name', 'EncyclopediaBot'),
            config('app.url', 'http://localhost'),
            config('mail.from.address', 'admin@example.com')
        );

        $response = Http::timeout(30)
            ->withHeaders([
                'User-Agent' => $userAgent,
                'Accept' => 'application/json',
            ])
            ->get("https://{$lang}.wikipedia.org/w/api.php", [
                'action' => 'parse',
                'page' => $title,
                'prop' => 'text|displaytitle',
                'format' => 'json',
                'formatversion' => 2,
                'disableeditsection' => true,
                'disabletoc' => true,
                'redirects' => 1,
            ]);

        if (!$response->ok()) {
            return null;
        }

        $data = $response->json();

        if (isset($data['error'])) {
            return null;
        }

        $parse = $data['parse'] ?? null;
        if (!$parse) {
            return null;
        }

        $displayTitle = strip_tags($parse['displaytitle'] ?? $title);
        $html = $parse['text'] ?? '';

        $cleanHtml = $this->cleanHtml($html);

        $excerpt = mb_substr(strip_tags($cleanHtml), 0, 300);

        return [
            'title' => $displayTitle,
            'content' => $cleanHtml,
            'excerpt' => $excerpt,
            'lang' => $lang,
        ];
    }

    private function cleanHtml(string $html): string
    {
        $trailingKeywords = [
            'Ссылки', 'Примечания', 'Литература', 'См\.\s*также', 'Источники',
            'See also', 'References', 'External links', 'Further reading', 'Notes',
            'Bibliography', 'Citations', 'Sources',
        ];
        $trailingPattern = '#<h[23][^>]*>\s*(?:<[^>]+>\s*)*(?:' . implode('|', $trailingKeywords) . ')#iu';
        if (preg_match($trailingPattern, $html, $m, PREG_OFFSET_CAPTURE)) {
            $html = substr($html, 0, $m[0][1]);
        }

        $html = preg_replace('#<sup[^>]*class="[^"]*reference[^"]*"[^>]*>.*?</sup>#si', '', $html);

        $html = preg_replace('#<span class="mw-editsection">.*?</span>#si', '', $html);

        $html = preg_replace('#<style[^>]*>.*?</style>#si', '', $html);

        foreach (['navbox', 'metadata', 'catlinks', 'mw-hidden', 'noprint', 'sistersitebox', 'mw-empty-elt', 'hatnote', 'shortdescription', 'reflist', 'refbegin'] as $cls) {
            $html = $this->removeBalancedTag($html, 'div', 'class', $cls);
        }

        $html = $this->removeBalancedTag($html, 'table', 'class', 'infobox');
        $html = $this->removeBalancedTag($html, 'table', 'class', 'wikitable');
        $html = $this->removeBalancedTag($html, 'table', 'class', 'sidebar');
        $html = $this->removeBalancedTag($html, 'table', 'class', 'ambox');

        $html = preg_replace('#<figure[^>]*>.*?</figure>#si', '', $html);
        $html = $this->removeBalancedTag($html, 'div', 'class', 'thumb');
        $html = $this->removeBalancedTag($html, 'div', 'class', 'floatright');
        $html = $this->removeBalancedTag($html, 'div', 'class', 'floatleft');

        $html = preg_replace('#<img[^>]*>#i', '', $html);

        $html = preg_replace('#<p>\s*</p>#', '', $html);

        $html = preg_replace('#\s+(class|style|id|data-[a-z-]+|role|aria-[a-z-]+|about|typeof|property)="[^"]*"#i', '', $html);

        $html = preg_replace('#<span[^>]*>(.*?)</span>#si', '$1', $html);

        $html = preg_replace('#^\s*<div[^>]*>\s*#', '', $html);
        $html = preg_replace('#\s*</div>\s*$#', '', $html);

        $html = preg_replace("#\n{3,}#", "\n\n", $html);

        return trim($html);
    }

    private function removeBalancedTag(string $html, string $tag, string $attr, string $value): string
    {
        $openPattern = '#<' . $tag . '\b[^>]*\b' . $attr . '\s*=\s*"[^"]*\b' . preg_quote($value, '#') . '\b[^"]*"[^>]*>#i';
        $offset = 0;
        while (preg_match($openPattern, $html, $m, PREG_OFFSET_CAPTURE, $offset)) {
            $start = $m[0][1];
            $cursor = $start + strlen($m[0][0]);
            $depth = 1;
            $len = strlen($html);
            while ($cursor < $len && $depth > 0) {
                $nextOpen = stripos($html, '<' . $tag, $cursor);
                $nextClose = stripos($html, '</' . $tag . '>', $cursor);
                if ($nextClose === false) {
                    break;
                }
                if ($nextOpen !== false && $nextOpen < $nextClose) {
                    $after = $html[$nextOpen + strlen($tag) + 1] ?? '';
                    if ($after === ' ' || $after === '>' || $after === "\t" || $after === "\n") {
                        $depth++;
                    }
                    $cursor = $nextOpen + strlen($tag) + 1;
                } else {
                    $depth--;
                    $cursor = $nextClose + strlen($tag) + 3;
                }
            }
            if ($depth === 0) {
                $html = substr($html, 0, $start) . substr($html, $cursor);
                $offset = $start;
            } else {
                $offset = $start + 1;
            }
        }
        return $html;
    }
}
