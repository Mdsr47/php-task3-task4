<?php
/**
 * Task 4: HTTP Request with Custom Headers
 * Sends a request to a public API with custom headers.
 * Bonus: Retry logic on failure (up to MAX_RETRIES attempts).
 *
 * API used: https://jsonplaceholder.typicode.com/posts/1  (free, no key needed)
 */

// ──────────────────────────────────────────────
// Config
// ──────────────────────────────────────────────
define('API_URL',     'https://jsonplaceholder.typicode.com/posts/1');
define('MAX_RETRIES', 3);
define('RETRY_DELAY', 2);   // seconds between retries

// ──────────────────────────────────────────────
// sendRequest() – with retry logic
// ──────────────────────────────────────────────
function sendRequest(string $url, array $headers, int $maxRetries = MAX_RETRIES): array {
    $attempt    = 0;
    $lastError  = '';
    $lastStatus = 0;

    while ($attempt < $maxRetries) {
        $attempt++;
        echo "  Attempt $attempt of $maxRetries ...\n";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        $body       = curl_exec($ch);
        $httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError  = curl_error($ch);
        curl_close($ch);

        // Success: 2xx status and no cURL error
        if (!$curlError && $httpStatus >= 200 && $httpStatus < 300) {
            return [
                'success'  => true,
                'status'   => $httpStatus,
                'body'     => $body,
                'attempts' => $attempt,
            ];
        }

        $lastError  = $curlError ?: "HTTP $httpStatus";
        $lastStatus = $httpStatus;

        echo "  ❌ Failed (Reason: $lastError). " .
             ($attempt < $maxRetries ? "Retrying in " . RETRY_DELAY . "s...\n" : "No more retries.\n");

        if ($attempt < $maxRetries) {
            sleep(RETRY_DELAY);
        }
    }

    return [
        'success'  => false,
        'status'   => $lastStatus,
        'body'     => null,
        'attempts' => $attempt,
        'error'    => $lastError,
    ];
}

// ──────────────────────────────────────────────
// Main
// ──────────────────────────────────────────────
echo "=== Task 4: HTTP Request with Custom Headers ===\n\n";

$customHeaders = [
    'User-Agent: PHPHttpClient/1.0 (PHP/' . PHP_VERSION . ')',
    'Accept: application/json',
    'X-Custom-Header: PHPTask4',
];

echo "Target URL : " . API_URL . "\n";
echo "Headers sent:\n";
foreach ($customHeaders as $h) {
    echo "  $h\n";
}
echo "\n";

$result = sendRequest(API_URL, $customHeaders);

echo "\n--- Result ---\n";
echo "HTTP Status : {$result['status']}\n";
echo "Attempts    : {$result['attempts']}\n";

if ($result['success']) {
    echo "Status      : ✅ Success\n\n";

    $decoded = json_decode($result['body'], true);

    if (json_last_error() === JSON_ERROR_NONE) {
        echo "API Response (parsed JSON):\n";
        echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "API Response (raw):\n" . $result['body'] . "\n";
    }
} else {
    echo "Status      : ❌ All retries failed\n";
    echo "Last Error  : {$result['error']}\n";
}