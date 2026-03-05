<?php
include 'functions/db_connection.php';

if (!isset($_GET['short_id'])) {
    http_response_code(404);
    echo "Link not found";
    exit;
}

$shortId = $_GET['short_id'];

$stmt = $conn->prepare("SELECT id, url FROM links WHERE short_id = ?");
$stmt->execute([$shortId]);
$link = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$link) {
    http_response_code(404);
    echo "Link not found";
    exit;
}

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$device = 'unknown';
if (strpos($userAgent, 'Mobile') !== false) {
    $device = 'mobile';
} elseif (strpos($userAgent, 'Tablet') !== false) {
    $device = 'tablet';
} else {
    $device = 'desktop';
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$location = 'unknown';
$operating_system = 'unknown';

if ($ip !== '127.0.0.1') {
    $url = "http://ip-api.com/json/{$ip}";
    $response = @file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['status']) && $data['status'] === 'success') {
            $location = ($data['country'] ?? 'unknown') . ', ' . ($data['city'] ?? 'unknown');
        }
    }
}

if (strpos($userAgent, 'Windows') !== false) {
    $operating_system = 'Windows';
} elseif (strpos($userAgent, 'Macintosh') !== false || strpos($userAgent, 'Mac OS') !== false) {
    $operating_system = 'macOS';
} elseif (strpos($userAgent, 'Linux') !== false) {
    $operating_system = 'Linux';
} elseif (strpos($userAgent, 'Android') !== false) {
    $operating_system = 'Android';
} elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
    $operating_system = 'iOS';
} else {
    $operating_system = 'unknown';
}

$stmt = $conn->prepare("INSERT INTO clicks (link_id, location, device, operating_system) VALUES (?, ?, ?, ?)");
$stmt->execute([$link['id'], $location, $device, $operating_system]);

header("Location: " . $link['url']);
exit;
?>