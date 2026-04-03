# PHP Tasks – Web Scraping & HTTP Requests

A small PHP project covering web scraping and HTTP requests with custom headers.

---

## 📁 Project Structure

```
phpproject/
├── task3_scraper.php       # Task 3 – Web scraper (quotes.toscrape.com)
├── task4_http_request.php  # Task 4 – HTTP request with custom headers + retry logic
├── quotes.json             # Auto-generated after running Task 3
└── README.md
```

---

## ⚙️ Requirements

| Requirement | Version |
|---|---|
| PHP | 7.4 or higher |
| Extensions | `curl`, `dom`, `libxml` (all bundled with standard PHP) |

### Check your PHP version
```bash
php -v
```

### Check required extensions are enabled
```bash
php -m | grep -E "curl|dom|libxml"
```

---

## 🚀 How to Run

### Clone the repository
```bash
git clone https://github.com/Mdsr47/php-task3-task4.git
cd php-task3-task4
```

### Task 3 – Web Scraper
### Task 3 and 4 you may test by using xampp(it's used)

```bash
php task3_scraper.php
```

**What it does:**
- Scrapes quotes and author names from `http://quotes.toscrape.com/`
- Loops through up to 5 pages (bonus multi-page scraping)
- Prints all quotes to the terminal
- Saves results to `quotes.json`

**Expected output (excerpt):**
```
=== Task 3: Web Scraper (quotes.toscrape.com) ===

Scraping page 1: http://quotes.toscrape.com/
  Found 10 quotes.
Scraping page 2: http://quotes.toscrape.com/page/2/
  Found 10 quotes.
...
--- Total quotes scraped: 50 ---

[1] "The world as we have created it is a process of our thinking..."
    — Albert Einstein
...
✅ Results saved to: quotes.json
```

**To scrape more/fewer pages**, open `task3_scraper.php` and change:
```php
$maxPages = 5;  // set to 10 for all pages, or 1 for just page 1
```

---

### Task 4 – HTTP Request with Custom Headers
```bash
php task4_http_request.php
```

**What it does:**
- Sends a GET request to `https://jsonplaceholder.typicode.com/posts/1` (a free public API)
- Sets custom headers: `User-Agent`, `Accept`, and `X-Custom-Header`
- Displays the HTTP status code and parsed JSON response
- **Bonus:** Retries up to 3 times (with a 2-second delay) if the request fails

**Expected output:**
```
=== Task 4: HTTP Request with Custom Headers ===

Target URL : https://jsonplaceholder.typicode.com/posts/1
Headers sent:
  User-Agent: PHPHttpClient/1.0 (PHP/8.x.x)
  Accept: application/json
  X-Custom-Header: PHPTask4

  Attempt 1 of 3 ...

--- Result ---
HTTP Status : 200
Attempts    : 1
Status      : ✅ Success

API Response (parsed JSON):
{
    "userId": 1,
    "id": 1,
    "title": "...",
    "body": "..."
}
```

---

## 🧪 Troubleshooting

| Problem | Fix |
|---|---|
| `curl_exec` returns false | Make sure the `curl` extension is enabled in `php.ini` |
| `DOMDocument` not found | Enable `extension=dom` in `php.ini` |
| Cannot connect to quotes.toscrape.com | Check your internet connection |
| Permission denied on `quotes.json` | Make sure the project folder is writable |

---

## 📝 Notes

- No external libraries or Composer packages are required — pure PHP only.
- Task 3 uses `cURL` for fetching and `DOMDocument + DOMXPath` for parsing HTML.
- Task 4 demonstrates retry logic: if the request returns a non-2xx status or a cURL error, it automatically retries up to `MAX_RETRIES` times.