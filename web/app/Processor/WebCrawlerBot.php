<?php

namespace App\Processor;

use GuzzleHttp\Client;
use DOMDocument;
use Exception;
use DiDom\Document;
use SplQueue;

/**
 * @package App\Processor
 */
class WebCrawlerBot
{

    private $baseUrl;

    public function __construct($url)
    {
        $this->baseUrl = $this->getBaseUrl($url);
    }

    /**
     * 内部リンクツリーを作成する
     *
     * @param string $url
     * @return array
     */
    public function createInternalLinkTree($url)
    {
        $visited = array();
        $queue = new SplQueue();
        $queue->enqueue(array('url' => $url, 'parent' => null, 'level' => 0));
        $visited[$url] = true;
        $result = collect();
        $depth = 0;

        while (!$queue->isEmpty()) {

            $current = $queue->dequeue();

            $url = $current['url'];
            $parent = $current['parent'];
            $level = $current['level'];

            if (isset($current['checked'])) {
                $result->push(array('url' => $url, 'parent' => $parent, 'level' => $level, 'text' => $this->extractTitle($url, $url, $level) . '!', "children" => []));
                continue;
            }

            // 30 回まで閲覧した場合は、アルゴリズムを停止します
            if ($depth >= 30) {
                $result->push(array('url' => $url, 'parent' => $parent, 'level' => $level, 'text' => $this->extractTitle($url, $url, $level) . '$', "children" => []));
                continue;
            }

            $html = $this->fetchHtml($url);
            $depth++;
            // ページ上のすべての内部リンクを取得します
            $links = $this->extractLinks($html, $this->baseUrl);

            foreach ($links as $link) {
                if ($link == $url) {
                    continue;
                }
                $childUrl = $this->normalizeUrl($link['url'], $this->baseUrl);
                if (!isset($visited[$childUrl])) {
                    //これらのリンクが参照されていない場合は、これらのリンクをキューに追加します
                    $queue->enqueue(array('url' => $childUrl, 'parent' => $url, 'level' => $level + 1));
                    $visited[$childUrl] = true;
                } else {
                    $queue->enqueue(array('url' => $childUrl, 'parent' => $url, 'level' => $level + 1, 'checked' => 1));
                }
            }

            // 結果に親パスを追加
            $result->push(['url' => $url,  'parent' => $parent, 'level' => $level, 'text' => $this->extractTitle($html, $url, $level), "children" => []]);

        }

        $result = $result->toArray();
        $levelMax = end($result);
        $levelMax = current($result);
        $result = $this->builTree($result, $levelMax['level'] + 1);

        return $result;
    }

    private function builTree($inputArray, $levelMax)
    {
        $outputArray = [];

        foreach ($inputArray as $item) {
            if ($item['parent'] === null) {
                $outputArray[] = $item;
            } else {
                $this->addToTree($outputArray, $item, $levelMax -1);
            }
        }

        return $outputArray;
    }

    private function addToTree(&$outputArray, $item, $levelMax)
    {
        if ($levelMax == 0) {
            return;
        }

        foreach ($outputArray as &$parent) {
            if ($parent['url'] === $item['parent']) {
                $parent['children'][] = $item;
                return;
            } else {
                if (!empty($parent['children'])) {
                    $this->addToTree($parent['children'], $item, $levelMax -1);
                }
            }
        }
    }

    // function convertArray($inputArray)
    // {
    //     $outputArray = [];

    //     foreach ($inputArray as $item) {
    //         if ($item['parent'] === null) {
    //             $outputArray[] = $item;
    //         } else {
    //             foreach ($outputArray as &$parent) {
    //                 if ($parent['url'] === $item['parent']) {
    //                     $parent['children'][] = $item;
    //                 } else {
    //                     foreach ($parent['children'] as &$child) {
    //                         if ($child['url'] === $item['parent']) {
    //                             $child['children'][] = $item;
    //                         } else {
    //                             foreach ($child['children'] as &$i) {
    //                                 if ($i['url'] === $item['parent']) {
    //                                     $i['children'][] = $item;
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     return $outputArray;
    // }

    private function extractTitle($var, $url, $level)
    {
        return $this->extractLinkText($var) . '(' . $this->getPath($url) . ')' ;
    }

    // public function createInternalLinkTree(string $url, int &$depth = 0, array $seenUrls = []): array
    // {
    //     // URL không hợp lệ
    //     if (!$this->isValidUrl($url)) {
    //         return [];
    //     }

    //     // Khác tên miền
    //     if (!$this->isSameDomain($url, $this->baseUrl)) {
    //         return [];
    //     }

    //     // Đánh dấu url đã được ghé thăm
    //     if ($depth == 0) {
    //         $seenUrls[] = $url;
    //     }

    //     // Lấy HTML
    //     $html = $this->fetchHtml($url);

    //     // Lấy tất cả các liên kết nội bộ trên trang
    //     $links = $this->extractLinks($html, $this->baseUrl);

    //     // Xây dựng cây nội liên kết
    //     $tree = [];
    //     foreach ($links as $link) {
    //         // リンクテキストを取得する
    //         $text = !empty($link['text']) ? $link['text'] : '**';
    //         $childUrl = $this->normalizeUrl($link['url'], $this->baseUrl);
    //         $childUrlPath = $this->getPath($childUrl);
    //         // 30回以上探索された場合は終了する
    //         if ($depth >= 3) {
    //             $fullText = "{$text}({$childUrlPath})$";
    //             $childTree = [];
    //         } elseif (in_array($childUrl, $seenUrls)) {
    //             $fullText = "{$text}({$childUrlPath})!";
    //             $childTree = [];
    //         } else {
    //             $fullText = "{$text}({$childUrlPath})";
    //             $seenUrls[] = $childUrl;
    //             $depth = $depth + 1;
    //             $childTree = $this->createInternalLinkTree($childUrl, $depth, $seenUrls);
    //         }

    //         // // Điều kiện dừng đệ quy khi link đã được ghé thăm hoặc đã đạt độ sâu tối đa
    //         // if ($depth >= 30 || in_array($childUrl, $seenUrls)) {
    //         //     continue;
    //         // }

    //         // // Đánh dấu url con đã được ghé thăm
    //         // $seenUrls[] = $childUrl;

    //         // // Đệ quy để lấy cây nội liên kết của url con
    //         // $childDepth = $depth + 1;
    //         // $childTree = $this->createInternalLinkTree($childUrl, $childDepth, $seenUrls);

    //         // 子ノードを追加する
    //         $tree[] = [
    //             'url' => $childUrl,
    //             'text' => $fullText,
    //             'children' => $childTree
    //         ];
    //     }

    //     // Đảo ngược cây nội liên kết để có thể hiển thị theo yêu cầu
    //     // $tree = array_reverse($tree);

    //     // Trả về nút cha với các nút con đã được đảo ngược
    //     return [
    //         'url' => $url,
    //         'text' => $this->extractLinkText($html) . '(' . $this->getPath($url) . ')',
    //         'children' => $tree
    //     ];
    // }

    private function getPath(string $url): string
    {
        // Parse the URL to get its components
        $urlComponents = parse_url($url);

        // Extract the path component of the URL
        $path = $urlComponents['path'] ?? '';

        // Return the path
        return $path;
    }

    private function fetchHtml(string $url): string
    {
        $client = new Client();
        $response = $client->request('GET', $url);
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            throw new Exception("Failed to fetch HTML content from {$url}. Got HTTP status code {$statusCode}");
        }
        $html = $response->getBody()->getContents();

        return $html;
    }

    private function normalizeUrl(string $link, string $baseUrl): ?string
    {
        // Remove whitespace and convert HTML entities
        $link = trim(html_entity_decode($link));

        // Check if the link is already an absolute URL
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            return $link;
        }

        // Parse the base URL and link
        $baseUrlParts = parse_url($baseUrl);
        $linkParts = parse_url($link);

        // If the link is a path, combine with the base URL
        if (empty($linkParts['host'])) {
            $path = ltrim($linkParts['path'], '/');
            $link = sprintf('%s://%s/%s', $baseUrlParts['scheme'], $baseUrlParts['host'], $path);
        }

        // Remove default ports
        $link = str_replace(':80/', '/', $link);
        $link = str_replace(':443/', '/', $link);

        // Remove trailing slash from the domain and path
        $link = rtrim($link, '/');

        // Check if the link is still within the same domain as the base URL
        if ($linkParts['host'] !== $baseUrlParts['host']) {
            return null;
        }

        return $link;
    }

    /**
     * リンクからテキストを抽出する
     *
     * @param string $link リンクURL
     * @return string リンクテキスト（パス）
     */
    private function extractLinkText($variable)
    {
        if ($variable instanceof \Illuminate\Support\Str) {
            $html = $this->fetchHtml($variable);
        } else {
            $html = $variable;
        }
        $doc = new DOMDocument();
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $title = '';
        $h1 = '';
        $h2 = '';
        $alt = '';

        // extract title
        $titleElement = $doc->getElementsByTagName('title')->item(0);
        if ($titleElement !== null) {
            $title = trim($titleElement->nodeValue);
        }

        // extract H1
        $h1Element = $doc->getElementsByTagName('h1')->item(0);
        if ($h1Element !== null) {
            $h1 = trim($h1Element->nodeValue);
        }

        // extract H2
        $h2Element = $doc->getElementsByTagName('h2')->item(0);
        if ($h2Element !== null) {
            $h2 = trim($h2Element->nodeValue);
        }

        // extract alt text from images inside the link
        $imgElements = $doc->getElementsByTagName('img');
        foreach ($imgElements as $imgElement) {
            $parentElement = $imgElement->parentNode;
            if ($parentElement->tagName === 'a') {
                $alt = trim($imgElement->getAttribute('alt'));
                break;
            }
        }

        // use the first non-empty value in the order of title > H1 > H2 > alt
        $linkText = $title ?: ($h1 ?: ($h2 ?: ($alt ?: '**')));
        return $linkText;
    }

    private function isInternalLink($link, $baseUrl)
    {
        $parsedLink = parse_url($link);
        $parsedBaseUrl = parse_url($baseUrl);

        if (!isset($parsedLink['scheme']) || !isset($parsedLink['host'])) {
            // If the link doesn't have a scheme or host, it's not a valid URL
            return false;
        }

        if ($parsedLink['scheme'] !== $parsedBaseUrl['scheme'] || $parsedLink['host'] !== $parsedBaseUrl['host']) {
            // If the link's scheme or host doesn't match the base URL's scheme or host, it's not an internal link
            return false;
        }

        return true;
    }

    //URL が有効かどうかを確認する
    // private function isValidUrl($url)
    // {
    //     // URLの形式が正しいかどうかを判定する
    //     if (!filter_var($url, FILTER_VALIDATE_URL)) {
    //         return false;
    //     }

    //     // スキームがhttpまたはhttps以外の場合は無効とする
    //     $url_components = parse_url($url);
    //     $scheme = $url_components['scheme'];
    //     if ($scheme !== 'http' && $scheme !== 'https') {
    //         return false;
    //     }

    //     // ドメイン部分が存在しない場合は無効とする
    //     if (!isset($url_components['host'])) {
    //         return false;
    //     }

    //     return true;
    // }


    // private function isSameDomain($url, $base_url)
    // {
    //     $url_components = parse_url($url);
    //     $base_components = parse_url($base_url);
    //     if (!isset($url_components['host']) || !isset($base_components['host'])) {
    //         return false;
    //     }
    //     return $url_components['host'] === $base_components['host'];
    // }

    private function extractLinks(string $html, string $baseUrl)
    {
        $document = new Document($html);
        $links = [];

        foreach ($document->find('a') as $a) {
            $href = $a->getAttribute('href');
            if ($href !== null) {
                if (strpos($href, '#') !== false) {
                    continue; // skip links with a fragment identifier
                }
                $absoluteUrl = $this->resolveUrl($href, $baseUrl);
                if (!$this->isInternalLink($absoluteUrl, $baseUrl)) {
                    continue; // skip external links
                }
                $links[] = [
                    'url' => $absoluteUrl,
                    'text' => $a->text(),
                ];
            }
        }
        return $links;
    }

    /**
     * 相対URLを含むURLを絶対URLに変換
     *
     * @param string $url The URL to normalize and resolve.
     * @param string $baseUrl The base URL to resolve relative URLs against.
     * @return string|null The normalized and resolved URL, or null if the URL is not valid.
     */
    private function resolveUrl(string $url, string $baseUrl): ?string
    {
        // Normalize the URL
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        // Parse the base URL
        $baseUrlParts = parse_url($baseUrl);
        if ($baseUrlParts === false) {
            return null;
        }

        // Parse the URL
        $urlParts = parse_url($url);
        if ($urlParts === false) {
            return null;
        }

        // Handle relative URLs
        if (!isset($urlParts['scheme'])) {
            $urlParts['scheme'] = $baseUrlParts['scheme'] ?? '';
        }
        if (!isset($urlParts['host'])) {
            $urlParts['host'] = $baseUrlParts['host'] ?? '';
        }
        if (isset($urlParts['path']) && strpos($urlParts['path'], '/') !== 0) {
            $basePath = $baseUrlParts['path'] ?? '/';
            $urlParts['path'] = rtrim($basePath, '/') . '/' . ltrim($urlParts['path'], '/');
        }

        // Reconstruct the URL
        $scheme = $urlParts['scheme'] ?? '';
        $user = $urlParts['user'] ?? '';
        $pass = $urlParts['pass'] ?? '';
        $host = $urlParts['host'] ?? '';
        $port = isset($urlParts['port']) ? ':' . $urlParts['port'] : '';
        $path = $urlParts['path'] ?? '';
        $query = isset($urlParts['query']) ? '?' . $urlParts['query'] : '';
        $fragment = isset($urlParts['fragment']) ? '#' . $urlParts['fragment'] : '';

        return sprintf('%s://%s%s%s%s%s%s', $scheme, $user, ($user && $pass) ? ':' : '', $pass, ($user || $pass) ? '@' : '', $host . $port, $path . $query . $fragment);
    }

    private function getBaseUrl($url)
    {
        $parsedUrl = parse_url($url);
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';

        return $scheme . $host . $port;
    }
};
