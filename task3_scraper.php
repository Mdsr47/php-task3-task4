<?php
/**
 * Task 3: Basic Web Scraping
 * Scrapes quotes and authors from http://quotes.toscrape.com/
 * Bonus: Scrapes multiple pages
 */

function scrapeQuotes(string $url): array {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; PHPScraper/1.0)',
    ]);

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error || $httpCode !== 200) {
        echo "cURL error (HTTP $httpCode): $error\n";
        return [];
    }

    // Suppress HTML parse warnings
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath  = new DOMXPath($dom);
    $quotes = [];

    // Each quote lives in a <div class="quote">
    $quoteNodes = $xpath->query('//div[contains(@class,"quote")]');

    foreach ($quoteNodes as $node) {
        $textNode   = $xpath->query('.//span[@class="text"]', $node)->item(0);
        $authorNode = $xpath->query('.//small[@class="author"]', $node)->item(0);

        if ($textNode && $authorNode) {
            $quotes[] = [
                'quote'  => trim($textNode->textContent, '""\'"" '),
                'author' => trim($authorNode->textContent),
            ];
        }
    }

    return $quotes;
}

function hasNextPage(string $html): string|false {
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath    = new DOMXPath($dom);
    $nextLink = $xpath->query('//li[contains(@class,"next")]/a/@href')->item(0);

    return $nextLink ? $nextLink->nodeValue : false;
}

// ──────────────────────────────────────────────
// Main – scrape up to MAX_PAGES pages
// ──────────────────────────────────────────────
$baseUrl   = 'http://quotes.toscrape.com';
$currentUrl = $baseUrl . '/';
$allQuotes  = [];
$maxPages   = 5;          // change to scrape more
$page       = 1;

echo "=== Task 3: Web Scraper (quotes.toscrape.com) ===\n\n";

while ($currentUrl && $page <= $maxPages) {
    echo "Scraping page $page: $currentUrl\n";

    // Fetch raw HTML for next-page detection
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $currentUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; PHPScraper/1.0)',
    ]);
    $html = curl_exec($ch);
    curl_close($ch);

    $pageQuotes = scrapeQuotes($currentUrl);
    $allQuotes  = array_merge($allQuotes, $pageQuotes);

    echo "  Found " . count($pageQuotes) . " quotes.\n";

    $nextPath  = hasNextPage($html);
    $currentUrl = $nextPath ? $baseUrl . $nextPath : false;
    $page++;
}

echo "\n--- Total quotes scraped: " . count($allQuotes) . " ---\n\n";

// Display results
foreach ($allQuotes as $i => $q) {
    $num = $i + 1;
    echo "[$num] \"{$q['quote']}\"\n    — {$q['author']}\n\n";
}

// Save to JSON
$outputFile = __DIR__ . '/quotes.json';
file_put_contents($outputFile, json_encode($allQuotes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\n✅ Results saved to: quotes.json\n";