<?php

// Andere classes binnen trekken
require_once __DIR__ . '/Click.php';
require_once __DIR__ . '/Link.php';

class Redirector {
    private $conn;
    private $linkModel;
    private $clickModel;

    public function __construct($db) 
    {
        $this->conn = $db;
        $this->linkModel = new Link($db);
        $this->clickModel = new Click($db);
    }

    // valideren en terug geven
    public function getShortIdFromRequest() 
    {
        return isset($_GET['short_id']) ? trim($_GET['short_id']) : null;
    }

    // link resolven bij sort id
    public function resolveLink($shortId) 
    {
        return $this->linkModel->getByShortId($shortId) ?: null;
    }

    // device en user agent ophalen/detecteren
    public function detectDevice($userAgent) 
    {
        if (strpos($userAgent, 'Mobile') !== false) return 'mobile';
        if (strpos($userAgent, 'Tablet') !== false) return 'tablet';
        return 'desktop';
    }

    // operating system bij user agent sorteren
    public function detectOperatingSystem($userAgent) 
    {
        if (strpos($userAgent, 'Windows') !== false) return 'Windows';
        if (strpos($userAgent, 'Macintosh') !== false || strpos($userAgent, 'Mac OS') !== false) return 'macOS';
        if (strpos($userAgent, 'Linux') !== false) return 'Linux';
        if (strpos($userAgent, 'Android') !== false) return 'Android';
        if (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) return 'iOS';
        return 'unknown';
    }

    // locatie bij een ip zoeken via ip-api
    public function detectLocation($ip) 
    {
        if ($ip === '127.0.0.1' || $ip === '::1') return 'unknown';

        $url = "http://ip-api.com/json/" . urlencode($ip);
        $response = @file_get_contents($url);
        if (!$response) return 'unknown';

        $data = json_decode($response, true);
        if (!is_array($data) || ($data['status'] ?? '') !== 'success') return 'unknown';

        $country = $data['country'] ?? 'unknown';
        $city = $data['city'] ?? 'unknown';
        return $country . ', ' . $city;
    }

    // click opslaan en redirecten
    public function recordAndRedirect($link)
     {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $device = $this->detectDevice($userAgent);
        $operating_system = $this->detectOperatingSystem($userAgent);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $location = $this->detectLocation($ip);

        $this->clickModel->recordClick($link['id'], $location, $device, $operating_system);

        header('Location: ' . $link['url']);
        exit;
    }

    // top functie die de flow bepaald
    public function handleRequest(): void 
    {
        $shortId = $this->getShortIdFromRequest();
        if (!$shortId) {
            http_response_code(404);
            echo 'Link not found';
            exit;
        }

        $link = $this->resolveLink($shortId);
        if (!$link) {
            http_response_code(404);
            echo 'Link not found';
            exit;
        }

        $this->recordAndRedirect($link);
    }
}
