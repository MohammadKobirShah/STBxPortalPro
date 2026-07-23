<?php
require_once 'kobir.php';
global $SCARLET_WITCH, $MJ, $DARK_SIDE;

$TOKEN = "KOBIR_STREAM";
$PROXY_UA = 'Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3';

function live_proxy_config() {
    global $MJ;
    $proxy = '';
    if (is_array($MJ) && !empty($MJ['ProxyServer'])) $proxy = trim((string)$MJ['ProxyServer']);
    if ($proxy === '') return null;
    if (!preg_match('#^(?:(?<user>[^:@]+):(?<pass>[^@]+)@)?(?<host>[^:]+):(?<port>\d+)$#', $proxy, $m)) return null;
    return ['host' => $m['host'], 'port' => (int)$m['port'], 'user' => $m['user'] ?? '', 'pass' => $m['pass'] ?? ''];
}

function proxy_fetch($url, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    $proxy = live_proxy_config();
    if (!empty($proxy['host']) && !empty($proxy['port'])) {
        curl_setopt($ch, CURLOPT_PROXY, $proxy['host']);
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
        if (!empty($proxy['user'])) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['user'] . ':' . $proxy['pass']);
        }
    }
    $data = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ['data' => $data, 'info' => $info];
}

function build_live_url($params = []) {
    $params['token'] = $GLOBALS['TOKEN'];
    return 'live.php?' . http_build_query($params);
}

if (!empty($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
    if (preg_match('/^[A-Za-z0-9+\/=_-]+$/', $id) && !is_numeric($id) && strpos($id, 'http') === false) {
        $decoded = @base64_decode($id, true);
        if ($decoded !== false && stripos($decoded, 'http') === 0) {
            $stream = $decoded;
        } else {
            $ANTMAN = json_decode(secure_kobir($id), true);
            $stream = $ANTMAN['KOBIR']['cmd'] ?? '';
        }
    } else {
        $ANTMAN = json_decode(secure_kobir($id), true);
        if (!empty($ANTMAN['KOBIR']['Statuscode']) && $ANTMAN['KOBIR']['Statuscode'] !== 200 && stripos(($ANTMAN['KOBIR']['data'] ?? ''), 'Authorization failed') !== false) {
            @unlink($DARK_SIDE . "/login.kobir");
            @cleanup_session_cache($DARK_SIDE);
            header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        }
        $stream = $ANTMAN['KOBIR']['cmd'] ?? '';
    }

    if (stripos($stream, "http") !== 0) {
        $fallback = $SCARLET_WITCH['meta_data']['fallback_video'] ?? '';
        if ($fallback !== '') { header("Location: " . $fallback); exit; }
        http_response_code(404);
        exit("Stream unavailable");
    }

    $proxyMode = strtoupper($MJ['Proxy'] ?? 'DIRECT');
    if ($proxyMode !== 'PROXY') {
        header("Location: " . $stream);
        exit;
    }

    $headers = ['User-Agent: ' . $PROXY_UA, 'Referer: ' . (rtrim($MJ['URL'] ?? '', '/') . '/c/')];
    $res = proxy_fetch($stream, $headers);
    $status = (int)($res['info']['http_code'] ?? 0);
    $ctype = $res['info']['content_type'] ?? 'application/vnd.apple.mpegurl';

    if (in_array($status, [301, 302, 303, 307, 308], true) && !empty($res['info']['redirect_url'])) {
        header("Location: " . build_live_url(['id' => base64_encode($res['info']['redirect_url'])]));
        exit;
    }

    if ($status >= 400) {
        header("Location: " . $stream);
        exit;
    }

    $body = (string)$res['data'];
    if (stripos($ctype, 'mpegurl') !== false || stripos($body, '#EXTM3U') === 0) {
        header("Content-Type: application/vnd.apple.mpegurl");
        $segBase = str_contains($stream, '?') ? substr($stream, 0, strrpos($stream, '/') + 1) : dirname(explode('?', $stream)[0]) . '/';
        $manifestBase = $segBase;
        $out = '';
        foreach (preg_split('/\r?\n/', $body) as $line) {
            if ($line === '') { $out .= "\n"; continue; }
            if (strpos($line, 'URI="') !== false) {
                $line = preg_replace_callback('/URI="([^"]+)"/', function($m) use ($manifestBase) {
                    $u = $m[1];
                    if (stripos($u, 'http') !== 0) $u = $manifestBase . ltrim($u, '/');
                    return 'URI="' . build_live_url(['enc' => scarlet_witch('encrypt', $u)]) . '"';
                }, $line);
            } elseif (stripos($line, '.m3u8') !== false && $line[0] !== '#') {
                $u = trim($line);
                if (stripos($u, 'http') !== 0) $u = $manifestBase . ltrim($u, '/');
                $line = build_live_url(['enc' => scarlet_witch('encrypt', $u)]);
            } elseif ((stripos($line, '.ts') !== false || stripos($line, '.m4s') !== false || stripos($line, '.mp4') !== false) && $line[0] !== '#') {
                $u = trim($line);
                if (stripos($u, 'http') !== 0) $u = $segBase . ltrim($u, '/');
                $line = build_live_url(['seg' => scarlet_witch('encrypt', $u)]);
            }
            $out .= $line . "\n";
        }
        echo str_replace("#EXTM3U", "#EXTM3U\n#DEVELOPED_BY_KOBIR_SHAH_DEV", $out);
        exit;
    }

    header("Content-Type: " . $ctype);
    echo $body;
    exit;
}

if (!empty($_REQUEST['enc']) && ($_REQUEST['token'] ?? '') === $TOKEN) {
    $url = scarlet_witch('decrypt', (string)$_REQUEST['enc']);
    if (stripos($url, 'http') !== 0) { http_response_code(400); exit("Invalid manifest"); }
    $headers = ['User-Agent: ' . $PROXY_UA];
    $res = proxy_fetch($url, $headers);
    $body = (string)$res['data'];
    header("Content-Type: application/vnd.apple.mpegurl");
    $segBase = str_contains($url, '?') ? substr($url, 0, strrpos($url, '/') + 1) : dirname(explode('?', $url)[0]) . '/';
    $out = '';
    foreach (preg_split('/\r?\n/', $body) as $line) {
        if ($line === '') { $out .= "\n"; continue; }
        if (strpos($line, 'URI="') !== false) {
            $line = preg_replace_callback('/URI="([^"]+)"/', function($m) use ($segBase) {
                $u = $m[1]; if (stripos($u, 'http') !== 0) $u = $segBase . ltrim($u, '/');
                return 'URI="' . build_live_url(['key' => scarlet_witch('encrypt', $u)]) . '"';
            }, $line);
        } elseif ((stripos($line, '.ts') !== false || stripos($line, '.m4s') !== false || stripos($line, '.mp4') !== false || stripos($line, '.m3u8') !== false) && $line[0] !== '#') {
            $u = trim($line);
            if (stripos($u, 'http') !== 0) $u = $segBase . ltrim($u, '/');
            $key = stripos($u, '.m3u8') !== false ? 'enc' : 'seg';
            $line = build_live_url([$key => scarlet_witch('encrypt', $u)]);
        }
        $out .= $line . "\n";
    }
    echo str_replace("#EXTM3U", "#EXTM3U\n#DEVELOPED_BY_KOBIR_SHAH_DEV", $out);
    exit;
}

if ((!empty($_REQUEST['seg']) || !empty($_REQUEST['key'])) && ($_REQUEST['token'] ?? '') === $TOKEN) {
    $enc = $_REQUEST['seg'] ?? $_REQUEST['key'];
    $url = scarlet_witch('decrypt', (string)$enc);
    if (stripos($url, 'http') !== 0) { http_response_code(400); exit("Invalid segment"); }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: ' . $PROXY_UA]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $data = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    header("Content-Type: " . ($info['content_type'] ?? 'video/mp2t'));
    header("Content-Length: " . strlen((string)$data));
    echo $data;
    exit;
}

$fallback = $SCARLET_WITCH['meta_data']['fallback_video'] ?? '';
if ($fallback !== '') { header("Location: " . $fallback); exit; }
http_response_code(404);
exit("Not found");
