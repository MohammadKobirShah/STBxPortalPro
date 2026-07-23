<?php
header('Content-Type: audio/x-mpegurl');
header('Content-Disposition: inline; filename="rkdy-stalker.m3u"');
require_once 'kobir.php';
global $SCARLET_WITCH, $DARK_SIDE;
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$currentPath = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
$baseUrl = $protocol . $host . rtrim($currentPath, '/') . "/";

if (!file_exists($DARK_SIDE . "/login.kobir")) {
    echo "#EXTM3U\n#EXTINF:-1,Session Missing - Please login first\n";
    exit;
}

$channelsRaw = json_fetcher();
$channels = json_decode($channelsRaw, true);
if (!is_array($channels)) {
    echo "#EXTM3U\n#EXTINF:-1,Playlist fetch failed\n";
    exit;
}

echo "#EXTM3U\n#EXT-X-VERSION:3\n";
echo "#EXT-X-SESSION-DATA:DATA-ID=\"com.kobirshah.build\" VALUE=\"RKDY-STALKER-PRO\"\n";
if (!empty($SCARLET_WITCH['addon_service']) && is_array($SCARLET_WITCH['addon_service'])) {
    $ctx = stream_context_create(['http'=>['timeout'=>3,'ignore_errors'=>true],'ssl'=>['verify_peer'=>false,'verify_peer_name'=>false]]);
    foreach ($SCARLET_WITCH['addon_service'] as $url) {
        $c = @file_get_contents($url, false, $ctx);
        if ($c) echo trim($c) . "\n";
    }
}
foreach ($channels as $ch) {
    if (!is_array($ch)) continue;
    $name = $ch['Name'] ?? 'Unknown';
    $cid  = $ch['playback_url'] ?? '';
    $logo = $ch['logo'] ?? '';
    $genre = $ch['genre'] ?? 'Uncategorized';
    $num = $ch['number'] ?? '';
    $tvg = $ch['xmltv_id'] ?? $cid;
    $attrs = [];
    $attrs[] = 'tvg-id="' . addcslashes((string)$tvg, '"') . '"';
    if ($num !== '') $attrs[] = 'tvg-chno="' . addcslashes((string)$num, '"') . '"';
    $attrs[] = 'tvg-logo="' . addcslashes((string)$logo, '"') . '"';
    $attrs[] = 'group-title="' . addcslashes((string)$genre, '"') . '"';
    echo "#EXTINF:-1 " . implode(' ', $attrs) . "," . $name . "\n";
    echo $baseUrl . "live.php?id=" . rawurlencode((string)$cid) . "\n";
}
