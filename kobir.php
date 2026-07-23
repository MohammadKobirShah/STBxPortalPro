<?php
error_reporting(0);
date_default_timezone_set("Asia/Dhaka");
$DARK_SIDE = "secure_kobir";
$LIGHT_SIDE = "cache_kobir";
if (!is_dir($DARK_SIDE)) {
    @mkdir($DARK_SIDE, 0777, true);
}
if (!is_dir($LIGHT_SIDE)) {
    @mkdir($LIGHT_SIDE, 0755, true);
}
if (!file_exists($DARK_SIDE . "/.htaccess")) {
    @file_put_contents($DARK_SIDE . "/.htaccess", "Deny from all");
}
if (!file_exists($DARK_SIDE . "/index.php")) {
    @file_put_contents($DARK_SIDE . "/index.php", "<?php http_response_code(403); exit('Access Denied'); ?>");
}
$MJ = @json_decode((string)@file_get_contents($DARK_SIDE . "/login.kobir"), true);
if (!is_array($MJ)) $MJ = [];
$MJ['ProxyServer'] = $MJ['ProxyServer'] ?? '';
header("access-control-allow-origin: *");
header("access-control-allow-headers: content-type, x-developed-by, x-powered-by, x-github-username, x-timestamp, x-readable-time");
header("Access-Control-Allow-Methods: GET, POST");
$SCARLET_WITCH = kobir_meta();
auth();

function auth()
{
    global $DARK_SIDE, $MJ;
    $NATASHA = $DARK_SIDE . "/token.kobir";
    $DOCTOR_DOOM = $MJ;
    if (file_exists($NATASHA)) {
        $savedData = json_decode((string)@file_get_contents($NATASHA), true);
        $oldMac = $savedData['KOBIR']['mac'] ?? '';
        $oldUrl = $savedData['KOBIR']['URL'] ?? '';
        $newUrl = rtrim(($DOCTOR_DOOM['URL'] ?? ''), '/') . "/c/";
        if ($oldMac !== ($DOCTOR_DOOM['MAC'] ?? '') || $oldUrl !== $newUrl) {
            @cleanup_session_cache($DARK_SIDE);
        }
    }
}

function cleanup_session_cache($dir)
{
    @unlink($dir . "/live.kobir");
    @unlink($dir . "/token.kobir");
    @unlink($dir . "/genre.json");
}

function normalize_portal_url($url)
{
    $url = trim((string)$url);
    if ($url === '') return '';
    if (!preg_match('#^https?://#i', $url)) $url = 'http://' . $url;
    $url = preg_replace('#/c/?$#i', '', rtrim($url, '/'));
    return $url;
}

function alt_scheme_url($url)
{
    if (stripos($url, 'http://') === 0) return 'https://' . substr($url, 7);
    if (stripos($url, 'https://') === 0) return 'http://' . substr($url, 8);
    return $url;
}

function portal_proxy_config()
{
    global $MJ;
    $proxy = '';
    if (is_array($MJ) && !empty($MJ['ProxyServer'])) {
        $proxy = trim((string)$MJ['ProxyServer']);
    }
    if ($proxy === '') return null;
    if (!preg_match('#^(?:(?<user>[^:@]+):(?<pass>[^@]+)@)?(?<host>[^:]+):(?<port>\d+)$#', $proxy, $m)) return null;
    return [
        'host' => $m['host'],
        'port' => (int)$m['port'],
        'user' => $m['user'] ?? '',
        'pass' => $m['pass'] ?? '',
        'raw' => $proxy,
    ];
}

function portal_cookie_jar()
{
    global $DARK_SIDE;
    return $DARK_SIDE . '/portal_cookies.dat';
}

function portal_warmup($portalUrl)
{
    static $warmed = [];
    $host = parse_url($portalUrl, PHP_URL_HOST) . '|' . parse_url($portalUrl, PHP_URL_PORT);
    if (isset($warmed[$host])) return true;
    $jar = portal_cookie_jar();
    $base = rtrim($portalUrl, '/');
    $urls = [$base . '/c/', $base . '/stalker_portal/c/'];
    foreach ($urls as $u) {
        $ch = curl_init($u);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (QtEmbedded; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
        curl_exec($ch);
        curl_close($ch);
    }
    $warmed[$host] = true;
    return true;
}

function portal_request($url, $headers, $method = 'GET', $body = null, $follow = 1)
{
    $jar = portal_cookie_jar();
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow ? 1 : 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    $proxy = portal_proxy_config();
    if (!empty($proxy['host']) && !empty($proxy['port'])) {
        curl_setopt($ch, CURLOPT_PROXY, $proxy['host']);
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
        if (!empty($proxy['user'])) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['user'] . ':' . $proxy['pass']);
        }
    }
    if (strtoupper((string)$method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    } else {
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
    }
    $data = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);
    return ['data' => $data, 'info' => $info, 'error' => $err];
}

function portal_request_with_fallback($url, $headers, $method = 'GET', $body = null)
{
    $host = parse_url($url, PHP_URL_HOST);
    @portal_warmup(parse_url($url, PHP_URL_SCHEME) . '://' . $host);
    $res = portal_request($url, $headers, $method, $body);
    $code = (int)($res['info']['http_code'] ?? 0);
    $raw = (string)($res['data'] ?? '');
    if ($code == 403 || $code == 0 || stripos($raw, 'You have been blocked') !== false) {
        $alt = alt_scheme_url($url);
        $res2 = portal_request($alt, $headers, $method, $body);
        $code2 = (int)($res2['info']['http_code'] ?? 0);
        if ($code2 > 0 && $code2 != 403 && stripos((string)($res2['data'] ?? ''), 'You have been blocked') === false) {
            return $res2;
        }
    }
    return $res;
}

function image($ROLEX)
{
    global $LIGHT_SIDE, $DARK_SIDE, $WANDA, $MJ, $SCARLET_WITCH;
    $DOCTOR_DOOM = $MJ;
    $WANDA = str_replace([".png", ".jpg", ".webp", ".jpeg"], ['', "", "", ""], $ROLEX);
    $portalBase = is_array($DOCTOR_DOOM) && !empty($DOCTOR_DOOM['URL']) ? rtrim($DOCTOR_DOOM['URL'], '/') : '';
    if (is_numeric($WANDA) && $portalBase !== '') {
        return $portalBase . '/misc/logos/320/' . ltrim($ROLEX, '/');
    }
    if (stripos((string)$ROLEX, "http") === 0) {
        return $ROLEX;
    }
    return is_array($SCARLET_WITCH) && isset($SCARLET_WITCH["meta_data"]["himg"]) ? $SCARLET_WITCH["meta_data"]["himg"] : '';
}

function id_generator($ROLEX)
{
    $cmd = (string)$ROLEX;
    $THOR = explode("/", $cmd);
    $host = $THOR[2] ?? '';
    if ($host === "localhost") {
        $cmd = str_ireplace('ffrt http://localhost/ch/', '', $cmd);
    } elseif ($host === "" || strpos($cmd, 'http:///ch/') !== false) {
        $cmd = str_ireplace('ffrt http:///ch/', '', $cmd);
    } elseif (stripos($cmd, "auto") !== false) {
        $cmd = base64_encode(str_ireplace("auto ", "", $cmd));
    }
    return trim($cmd);
}

function getMessage($CHRISTINE, $status_code)
{
    $js = is_array($CHRISTINE) ? ($CHRISTINE["js"] ?? []) : [];
    if (is_array($js) && !empty($js["msg"])) return (string)$js["msg"];
    if (is_array($js) && !empty($js["error"])) return (string)$js["error"];
    return match (true) {
        $status_code == 401 => "Unauthorized — login expired or invalid MAC.",
        $status_code == 403 => "Forbidden — portal blocked the request.",
        $status_code == 404 => "Not Found — endpoint missing on portal.",
        $status_code == 429 => "Rate limited — wait a few minutes then retry.",
        $status_code >= 500 => "Server error — portal is unstable.",
        empty($CHRISTINE) => "Empty response — no data received from portal.",
        default => "Unknown error occurred."
    };
}

function request_default_headers($profile, $token = '')
{
    $url = rtrim(($profile['URL'] ?? ''), '/');
    $mac = $profile['MAC'] ?? '';
    $sn = $profile['SN'] ?? '';
    $headers = [
        "User-Agent: Mozilla/5.0 (QtEmbedded; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3",
        "Accept: */*",
        "Accept-Language: en-US,en;q=0.9",
        "Connection: Keep-Alive",
        "Accept-Encoding: gzip, deflate",
        "X-User-Agent: Model: " . ($profile['Model'] ?? 'MAG254') . "; Link: WiFi",
        "Referer: " . $url . "/c/",
        "Origin: " . $url,
        "Cookie: mac=" . $mac . "; stb_lang=en; timezone=GMT; sn=" . $sn . "; did=" . ($profile['D1'] ?? '') . "; did2=" . ($profile['D2'] ?? ''),
    ];
    if ($token !== '') $headers[] = 'Authorization: Bearer ' . $token;
    return $headers;
}

function json_fetcher()
{
    global $DARK_SIDE, $MJ, $LIGHT_SIDE;
    $DOCTOR_DOOM = is_array($MJ) ? $MJ : [];
    if (empty($DOCTOR_DOOM['URL']) || empty($DOCTOR_DOOM['MAC'])) {
        return json_encode(["KOBIR" => ["message" => "No active session."]]);
    }
    $cacheFile = $DARK_SIDE . "/live.kobir";
    if (file_exists($cacheFile) && filesize($cacheFile) > 10 && (time() - filemtime($cacheFile)) < 1800) {
        vision_logs("Loading channels from local cache.");
        return (string)@file_get_contents($cacheFile);
    }
    $auth = validation();
    if (!is_array($auth) || empty($auth['Token'])) {
        return json_encode(["KOBIR" => ["message" => "Auth failed before fetching channels."]]);
    }
    $base = rtrim($DOCTOR_DOOM['URL'], '/');
    $THANOS = $base . "/server/load.php?type=itv&action=get_all_channels&JsHttpRequest=1-xml";
    $ROLEX = request_default_headers($DOCTOR_DOOM, $auth['Token']);
    $resp = portal_request_with_fallback($THANOS, $ROLEX, "GET");
    $CHRISTINE = json_decode((string)$resp["data"], true);
    $status_code = (int)($resp["info"]["http_code"] ?? 0);
    if (!is_array($CHRISTINE) || !isset($CHRISTINE["js"]["data"])) {
        return json_encode(["KOBIR" => ["message" => getMessage($CHRISTINE, $status_code), "Statuscode" => $status_code, "data" => $CHRISTINE, "raw" => $resp["data"] ?? '']]);
    }
    $genres = genre(true);
    $genreMap = is_array($genres) ? $genres : [];
    $out = [];
    foreach ($CHRISTINE["js"]["data"] as $item) {
        $gid = $item["tv_genre_id"] ?? '';
        $out[] = [
            "id" => $item['id'] ?? null,
            "Name" => $item['name'] ?? 'Unknown Channel',
            "number" => $item['number'] ?? '',
            "logo" => image($item['logo'] ?? ''),
            "cmd" => $item['cmd'] ?? '',
            "genre" => $genreMap[$gid] ?? "Uncategorized",
            "playback_url" => id_generator($item['cmd'] ?? ''),
            "tv_genre_id" => $gid,
            "xmltv_id" => $item['xmltv_id'] ?? '',
        ];
    }
    $encoded = json_encode($out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($encoded && count($out) > 0) {
        @file_put_contents($cacheFile, $encoded, LOCK_EX);
        return $encoded;
    }
    return json_encode(["KOBIR" => ["message" => getMessage($CHRISTINE, $status_code), "Statuscode" => $status_code, "data" => $CHRISTINE, "raw" => $resp["data"] ?? '']]);
}

function genre($returnMap = false)
{
    global $DARK_SIDE, $MJ;
    $DOCTOR_DOOM = is_array($MJ) ? $MJ : [];
    if (empty($DOCTOR_DOOM['URL'])) {
        return $returnMap ? [] : json_encode(["KOBIR" => ["message" => "No active portal."]]);
    }
    $auth = validation();
    $token = is_array($auth) ? ($auth['Token'] ?? '') : '';
    $base = rtrim($DOCTOR_DOOM['URL'], '/');
    $ROLEX = request_default_headers($DOCTOR_DOOM, $token);
    $resp = portal_request_with_fallback($base . "/server/load.php?type=itv&action=get_genres&JsHttpRequest=1-xml", $ROLEX, "GET");
    $CHRISTINE = json_decode((string)$resp["data"], true);
    $status_code = (int)($resp["info"]["http_code"] ?? 0);
    if (!is_array($CHRISTINE) || empty($CHRISTINE["js"])) {
        return $returnMap ? [] : json_encode(["KOBIR" => ["message" => getMessage($CHRISTINE, $status_code), "Statuscode" => $status_code]]);
    }
    $IRON_MAN = [];
    foreach ($CHRISTINE["js"] as $row) {
        if (isset($row["id"], $row["title"])) $IRON_MAN[(string)$row["id"]] = $row["title"];
    }
    return $returnMap ? $IRON_MAN : $IRON_MAN;
}

function scarlet_witch(string $action, string $data, string $passphrase = "KOBIR_SHAH")
{
    $cipher = "aes-256-cbc";
    $hash_algo = "sha256";
    $salt = hash("sha256", "KOBIR_PORTAL_SALT");
    $key = hash_pbkdf2($hash_algo, $passphrase, $salt, 10000, 32, true);
    if ($action === "encrypt") {
        $iv_length = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($iv_length);
        $ciphertext_raw = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext_raw === false) return '';
        $hmac = hash_hmac($hash_algo, $ciphertext_raw, $key, true);
        return bin2hex($iv . $hmac . $ciphertext_raw);
    }
    if ($action === "decrypt") {
        $data = trim($data);
        if ($data === '' || !ctype_xdigit($data) || strlen($data) % 2 !== 0) return '';
        $c = @hex2bin($data);
        if ($c === false) return '';
        $iv_length = openssl_cipher_iv_length($cipher);
        $hash_length = 32;
        if (strlen($c) < $iv_length + $hash_length + 1) return '';
        $iv = substr($c, 0, $iv_length);
        $hmac = substr($c, $iv_length, $hash_length);
        $ciphertext_raw = substr($c, $iv_length + $hash_length);
        $calcmac = hash_hmac($hash_algo, $ciphertext_raw, $key, true);
        if (hash_equals($hmac, $calcmac)) {
            $decoded = openssl_decrypt($ciphertext_raw, $cipher, $key, OPENSSL_RAW_DATA, $iv);
            return $decoded === false ? '' : $decoded;
        }
        return '';
    }
    return '';
}

function stream_link($id)
{
    global $DARK_SIDE, $MJ, $LIGHT_SIDE;
    $DOCTOR_DOOM = is_array($MJ) ? $MJ : [];
    if (empty($DOCTOR_DOOM['URL']) || $id === '') {
        return json_encode(["KOBIR" => ["message" => "Missing stream data."]]);
    }
    $auth = validation();
    $token = is_array($auth) ? ($auth['Token'] ?? '') : '';
    $base = rtrim($DOCTOR_DOOM['URL'], '/');
    $safeId = rawurlencode((string)$id);
    $THANOS = $base . '/server/load.php?type=itv&action=create_link&cmd=ffrt%20http%3A%2F%2Flocalhost%2Fch%2F' . $safeId . '&JsHttpRequest=1-xml';
    $ROLEX = request_default_headers($DOCTOR_DOOM, $token);
    $resp = portal_request_with_fallback($THANOS, $ROLEX, "GET");
    $CHRISTINE = json_decode((string)$resp["data"], true);
    $status_code = (int)($resp["info"]["http_code"] ?? 0);
    if (!empty($CHRISTINE["js"]["cmd"]) && $status_code == 200) {
        vision_logs("Stream Link Acquired for ID: $id");
        @file_put_contents($LIGHT_SIDE . "/" . preg_replace('/[^A-Za-z0-9_-]/', '_', (string)$id) . ".json", json_encode($CHRISTINE, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return json_encode(["KOBIR" => [
            "Author" => "KOBIR_SHAH",
            "generated_time" => time(),
            "message" => "Playback URL fetched successfully",
            "cmd" => $CHRISTINE["js"]["cmd"],
            "Statuscode" => $status_code,
            "data" => $CHRISTINE
        ]]);
    }
    return json_encode(["KOBIR" => ["message" => getMessage($CHRISTINE, $status_code), "Statuscode" => $status_code, "data" => $CHRISTINE, "raw" => $resp["data"] ?? '']]);
}

function validation()
{
    global $DARK_SIDE, $MJ;
    $loginFile = $DARK_SIDE . "/login.kobir";
    $tokenFile = $DARK_SIDE . "/token.kobir";
    if (!is_array($MJ) || empty($MJ['URL']) || empty($MJ['MAC'])) {
        return ["Token" => "", "URL" => "", "mac" => ""];
    }
    if (file_exists($tokenFile)) {
        $savedData = json_decode((string)@file_get_contents($tokenFile), true);
        $core = $savedData['KOBIR'] ?? null;
        if (is_array($core)) {
            $oldMac = $core['mac'] ?? '';
            $oldUrl = $core['URL'] ?? '';
            $newUrl = rtrim($MJ['URL'], '/') . "/c/";
            if ($oldMac !== $MJ['MAC'] || $oldUrl !== $newUrl) {
                cleanup_session_cache($DARK_SIDE);
            } else {
                $genTime = (int)($core['generated_time'] ?? 0);
                $ageMin = (time() - $genTime) / 60;
                if ($ageMin < 1400 && !empty($core['Token'])) {
                    vision_logs("Using cached token.");
                    return $core;
                }
            }
        }
    }
    $profile = json_decode(get_profile($MJ), true);
    $p = $profile['KOBIR'] ?? null;
    if (is_array($p) && !empty($p['Token']) && (($p['statusCode'] ?? 0) == 200 || ($p['Statuscode'] ?? 0) == 200)) {
        @file_put_contents($tokenFile, json_encode($profile), LOCK_EX);
        return $p;
    }
    return ["Token" => "", "URL" => rtrim($MJ['URL'], '/') . "/c/", "mac" => $MJ['MAC'], "message" => is_array($p) ? ($p['message'] ?? 'Auth failed') : 'Auth failed'];
}

function kobir_meta()
{
    $brandLogo = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCIgdmlld0JveD0iMCAwIDY0IDY0Ij48cmVjdCB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHJ4PSIxNCIgZmlsbD0iIzRmNDZlNSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTQlIiBmb250LWZhbWlseT0iQXJpYWwsc2Fucy1zZXJpZiIgZm9udC13ZWlnaHQ9IjkwMCIgZm9udC1zaXplPSIzNCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiPks8L3RleHQ+PC9zdmc+';
    $defaultPoster = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxOTIwIiBoZWlnaHQ9IjEwODAiIHZpZXdCb3g9IjAgMCAxOTIwIDEwODAiPjxyZWN0IHdpZHRoPSIxOTIwIiBoZWlnaHQ9IjEwODAiIGZpbGw9IiMwYjBiMWYiLz48cmFkaWFsR3JhZGllbnQgaWQ9ImciIGN4PSIyMCUiIGN5PSIyMCUiIHI9IjgwJSI+PHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iIzYzNjZmMSIgbGluZWN0eT0iMC4xIi8+PHN0b3Agb2Zmc2V0PSIxMDAlIiBzdG9wLWNvbG9yPSIjOGI1Y2Y2IiBzdG9wLW9wYWNpdHk9IjAiLz48L3JhZGlhbEdyYWRpZW50PjxyZWN0IHdpZHRoPSIxOTIwIiBoZWlnaHQ9IjEwODAiIGZpbGw9InVybCgjZykiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLHNhbnMtc2VyaWYiIGZvbnQtd2VpZ2h0PSI5MDAiIGZvbnQtc2l6ZT0iMTQwIiBmaWxsPSIjZmZmIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIiBvcGFjaXR5PSIwLjA4Ij5SS0RZPC90ZXh0Pjwvc3ZnPg==';
    $default = [
        'PORTAL_UNIVERSE' => [
            'DEV_NAME' => 'RKDY STALKER PRO | Kobir Shah',
            'x-developed-by' => 'KOBIR_SHAH_DEV',
            'X-POWERED-BY' => 'KOBIR-SHAH',
            'X-GITHUB-USERNAME' => 'kobirshah',
            'token' => 'KOBIR_PORTAL'
        ],
        'heartbeat_api' => ['heartbeat' => 'OFF', 'url' => ''],
        'api_endpoint' => [],
        'meta_data' => [
            'himg' => $brandLogo,
            'fallback_video' => '',
            'channel_img' => $brandLogo,
            'Rimg' => $brandLogo,
            'Limg' => '',
            'default_img' => $defaultPoster,
            'span' => ['R','K','D','Y',' ','S','T','A','L','K','E','R',' ','P','R','O']
        ],
        'addon_service' => [],
        'message' => []
    ];
    return $default;
}

function vision_logs($message, $level = "INFO")
{
    global $DARK_SIDE;
    $log_file = $DARK_SIDE . "/kobir.log";
    $timestamp = date("Y-m-d H:i:s");
    @file_put_contents($log_file, "[$timestamp] [$level] $message" . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function handshake($DOCTOR_DOOM, $token = '')
{
    $time = (int)time();
    if (!is_array($DOCTOR_DOOM) || empty($DOCTOR_DOOM['URL'])) {
        return json_encode(["KOBIR" => ["message" => "No portal URL configured.", "Statuscode" => 400]]);
    }
    $base = rtrim($DOCTOR_DOOM['URL'], '/');
    $THANOS = $base . "/server/load.php?type=stb&action=handshake&token=" . urlencode((string)$token) . "&JsHttpRequest=1-xml";
    $ROLEX = request_default_headers($DOCTOR_DOOM);
    $resp = portal_request_with_fallback($THANOS, $ROLEX, "GET");
    $CHRISTINE = json_decode((string)$resp["data"], true);
    $status_code = (int)($resp["info"]["http_code"] ?? 0);
    if (!empty($CHRISTINE["js"]["token"]) && $status_code == 200) {
        vision_logs("Handshake successful for " . $base);
        return json_encode(["KOBIR" => [
            "Author" => "KOBIR_SHAH",
            "generated_time" => $time,
            "message" => "Handshake token generated successfully.",
            "Token" => $CHRISTINE["js"]["token"],
            "Random" => $CHRISTINE["js"]["random"] ?? bin2hex(random_bytes(10)),
            "URL" => $base,
            "Statuscode" => $status_code,
            "data" => $CHRISTINE
        ]]);
    }
    return json_encode(["KOBIR" => [
        "Author" => "KOBIR_SHAH",
        "message" => getMessage($CHRISTINE, $status_code),
        "block_msg" => $CHRISTINE["js"]["block_msg"] ?? null,
        "Statuscode" => $status_code,
        "data" => $CHRISTINE,
        "raw" => $resp["data"] ?? ''
    ]]);
}

function get_profile($DOCTOR_DOOM, $token = '')
{
    global $SCARLET_WITCH, $DARK_SIDE;
    $time = time();
    if (!is_array($DOCTOR_DOOM) || empty($DOCTOR_DOOM['URL']) || empty($DOCTOR_DOOM['MAC'])) {
        return json_encode(["KOBIR" => ["message" => "URL and MAC are required.", "Statuscode" => 400]]);
    }
    $DOCTOR_DOOM['URL'] = normalize_portal_url($DOCTOR_DOOM['URL']);
    $base = rtrim($DOCTOR_DOOM['URL'], '/');
    if ($token === '' || !$token) {
        $hs = json_decode(handshake($DOCTOR_DOOM), true);
    } else {
        $hs = json_decode(handshake($DOCTOR_DOOM, $token), true);
    }
    if (empty($hs['KOBIR']['Token'])) return json_encode($hs);
    if (empty($token)) $time = time();
    $model = strtoupper($DOCTOR_DOOM['Model'] ?? 'MAG254');
    $modelNum = str_replace('MAG', '', $model);
    $params = [
        "type" => "stb",
        "action" => "get_profile",
        "hd" => 1,
        "ver" => "ImageDescription: 0.2.18-r14-pub-$modelNum; ImageDate: Fri Jan 15 15:20:44 EET 2016; PORTAL version: 5.1.0; API Version: JS API version: 328; STB API version: 134; Player Engine version: 0x566",
        "num_banks" => 2,
        "sn" => $DOCTOR_DOOM['SN'] ?? '',
        "stb_type" => $model,
        "image_version" => 218,
        "video_out" => "hdmi",
        "device_id" => $DOCTOR_DOOM['D1'] ?? '',
        "device_id2" => $DOCTOR_DOOM['D2'] ?? '',
        "signature" => '',
        "auth_second_step" => 1,
        "hw_version" => $DOCTOR_DOOM['hw_version'] ?? "1.7-BD-00",
        "not_valid_token" => 0,
        "client_type" => "STB",
        "hw_version_2" => $DOCTOR_DOOM['hw_version_2'] ?? md5($DOCTOR_DOOM['MAC']),
        "timestamp" => time(),
        "api_signature" => $DOCTOR_DOOM['API'] ?? '',
        "metrics" => json_encode([
            "mac" => $DOCTOR_DOOM['MAC'],
            "sn" => $DOCTOR_DOOM['SN'] ?? '',
            "model" => $model,
            "type" => "STB",
            "uid" => "",
            "random" => $hs['KOBIR']['Random'] ?? ''
        ]),
        "JsHttpRequest" => "1-xml"
    ];
    if (!empty($DOCTOR_DOOM['SG'])) $params["signature"] = $DOCTOR_DOOM['SG'];
    $THANOS = $base . "/server/load.php?" . http_build_query($params);
    $ROLEX = request_default_headers($DOCTOR_DOOM, $hs['KOBIR']['Token']);
    $resp = portal_request_with_fallback($THANOS, $ROLEX, "GET");
    $CHRISTINE = json_decode((string)$resp["data"], true);
    $exp  = !empty($CHRISTINE["js"]["expirydate"]) ? $CHRISTINE["js"]["expirydate"] : ($CHRISTINE["js"]["expire_billing_date"] ?? "Unlimited");
    $name = !empty($CHRISTINE["js"]["name"]) ? $CHRISTINE["js"]["name"] : ($CHRISTINE["js"]["fname"] ?? "Guest User");
    $status_code = (int)($resp["info"]["http_code"] ?? 0);
    $ok = !empty($CHRISTINE["js"]) && ($status_code == 200) && (!empty($CHRISTINE["js"]["password"]) || !empty($CHRISTINE["js"]["mac"]));
    if ($ok) {
        if (is_array($SCARLET_WITCH) && ($SCARLET_WITCH['heartbeat_api']["heartbeat"] ?? '') == "ON" && !empty($SCARLET_WITCH['heartbeat_api']["url"])) {
            @portal_request($SCARLET_WITCH['heartbeat_api']["url"], $ROLEX, "POST", json_encode($params));
        }
        $DOCTOR_DOOM['generated_time'] = $time;
        @file_put_contents($DARK_SIDE . "/login.kobir", json_encode($DOCTOR_DOOM, JSON_UNESCAPED_SLASHES), LOCK_EX);
        $host = parse_url($base, PHP_URL_HOST) ?: 'portal';
        @file_put_contents($DARK_SIDE . "/" . preg_replace('/[^A-Za-z0-9._-]/', '_', $host) . ".json", json_encode($DOCTOR_DOOM, JSON_UNESCAPED_SLASHES), LOCK_EX);
        $GLOBALS['MJ'] = $DOCTOR_DOOM;
        cleanup_session_cache($DARK_SIDE);
        $payload = [
            "KOBIR" => [
                "Author" => "KOBIR_SHAH",
                "message" => "Stalker data fetched successfully.",
                "generated_time" => $time,
                "Token" => $hs['KOBIR']['Token'],
                "device_id" => $DOCTOR_DOOM['D1'] ?? '',
                "device_id2" => $DOCTOR_DOOM['D2'] ?? '',
                "sig" => $DOCTOR_DOOM['SG'] ?? '',
                "ProxyServer" => $DOCTOR_DOOM['ProxyServer'] ?? '',
                "settings_password" => $CHRISTINE["js"]["parent_password"] ?? "0000",
                "Random" => $hs['KOBIR']['Random'],
                "URL" => $base . "/c/",
                "Name" => $name,
                "login" => $CHRISTINE["js"]["login"] ?? null,
                "Password" => $CHRISTINE["js"]["password"] ?? null,
                "parent_password" => $CHRISTINE["js"]["parent_password"] ?? "0000",
                "mac" => $CHRISTINE["js"]["mac"] ?? $DOCTOR_DOOM['MAC'],
                "expirydate" => $exp,
                "statusCode" => $status_code,
                "Statuscode" => $status_code,
                "Date" => date("Y-m-d H:i:s"),
                "data" => $CHRISTINE,
                "js" => $CHRISTINE["js"] ?? [],
                "ip" => $CHRISTINE["js"]["ip"] ?? "",
                "stb_type" => $model,
                "image_version" => 218,
                "D1" => $DOCTOR_DOOM['D1'] ?? '',
                "D2" => $DOCTOR_DOOM['D2'] ?? '',
                "SG" => $DOCTOR_DOOM['SG'] ?? '',
                "Model" => $model
            ]
        ];
        @file_put_contents($DARK_SIDE . "/token.kobir", json_encode($payload, JSON_UNESCAPED_SLASHES), LOCK_EX);
        return json_encode($payload);
    }
    return json_encode(["KOBIR" => [
        "Author" => "KOBIR_SHAH",
        "message" => getMessage($CHRISTINE, $status_code),
        "Statuscode" => $status_code ?: ($hs['KOBIR']['Statuscode'] ?? 500),
        "statusCode" => $status_code ?: ($hs['KOBIR']['Statuscode'] ?? 500),
        "raw" => $resp["data"] ?? '',
        "data" => $CHRISTINE
    ]]);
}

function heaven()
{
    global $SCARLET_WITCH;
    if (!empty($SCARLET_WITCH['api_endpoint']['img_cdn_url'])) {
        $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true], 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $raw = @file_get_contents($SCARLET_WITCH['api_endpoint']['img_cdn_url'], false, $ctx);
        if ($raw) {
            $arr = json_decode($raw, true);
            if (is_array($arr) && count($arr) > 0) return $arr[array_rand($arr)]["url"] ?? '';
        }
    }
    return "https://i.ibb.co/GQCh1t2b/Screenshot-2026-04-13-at-3-50-15-PM.png";
}

if (($_SERVER["REQUEST_METHOD"] ?? '') === "POST") {
    header('Content-Type: application/json');
    $rawInput = @file_get_contents('php://input');
    $TONY_STARK = json_decode((string)$rawInput, true);
    if (!is_array($TONY_STARK)) $TONY_STARK = $_POST;
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $headers = array_change_key_case(is_array($headers) ? $headers : [], CASE_LOWER);
    $expectedDev = $SCARLET_WITCH['PORTAL_UNIVERSE'] ?? [];
    $JPD   = ($headers['x-developed-by'] ?? '') === ($expectedDev['x-developed-by'] ?? 'KOBIR_SHAH_DEV');
    $PWR   = ($headers['x-powered-by'] ?? '') === ($expectedDev['X-POWERED-BY'] ?? 'KOBIR-SHAH');
    $GIT   = ($headers['x-github-username'] ?? '') === ($expectedDev['X-GITHUB-USERNAME'] ?? 'kobirshah');
    $devHeader = $headers['dev-name'] ?? '';
    $DEV    = $devHeader === ($expectedDev['DEV_NAME'] ?? 'KOBIR SHAH') || strtolower($devHeader) === strtolower((string)($expectedDev['DEV_NAME'] ?? 'KOBIR SHAH'));
    $AUTH_OK = ($JPD && $PWR && $GIT && $DEV);

    if (!$AUTH_OK) {
        http_response_code(403);
        echo json_encode(["error" => "Unauthorized: Signatures Not Verified"]);
        exit;
    }

    $ACTION = $_REQUEST['action'] ?? '';
    if ($ACTION === "livechannels") {
        $content = json_fetcher();
        $etag = md5($content);
        header("Cache-Control: public, max-age=300");
        header("ETag: \"$etag\"");
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === "\"$etag\"") {
            http_response_code(304);
            exit;
        }
        echo $content;
        exit;
    }

    if ($ACTION === "login_details") {
        $loginFile = $DARK_SIDE . "/login.kobir";
        $content = '';
        if (file_exists($loginFile)) {
            $login = json_decode((string)@file_get_contents($loginFile), true);
            if (is_array($login)) $content = get_profile($login);
        }
        if (empty($content)) {
            echo json_encode(["KOBIR" => ["statusCode" => 404, "message" => "No active session found. Please login again.", "ui_label" => "Session Expired"]]);
            exit;
        }
        $etag = md5($content);
        header("Cache-Control: no-store");
        header("ETag: \"$etag\"");
        echo $content;
        exit;
    }

    if ($ACTION === "login") {
        $url = normalize_portal_url($TONY_STARK['URL'] ?? '');
        $mac = strtoupper(trim((string)($TONY_STARK['MAC'] ?? '')));
        $sn  = trim((string)($TONY_STARK['SN'] ?? ''));
        if ($sn === '') $sn = strtoupper(substr(md5($mac), 0, 13));
        $proxy = strtoupper(trim((string)($TONY_STARK['Proxy'] ?? 'DIRECT')));
        if ($proxy === '' || $proxy === 'GO WEB PLAYER') $proxy = 'PROXY';
        $is_valid_url = filter_var($url, FILTER_VALIDATE_URL) !== false;
        $is_valid_mac = preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $mac) === 1;
        $is_valid_sn  = strlen($sn) >= 5;
        if ($is_valid_url && $is_valid_mac && $is_valid_sn) {
            $proxyServer = trim((string)($TONY_STARK['ProxyServer'] ?? ''));
            $profile = [
                'URL' => $url,
                'MAC' => $mac,
                'SN' => strtoupper($sn),
                'D1' => $TONY_STARK['D1'] ?? '',
                'D2' => $TONY_STARK['D2'] ?? '',
                'SG' => $TONY_STARK['SG'] ?? '',
                'Model' => strtoupper($TONY_STARK['Model'] ?? 'MAG254'),
                'API' => $TONY_STARK['API'] ?? '',
                'Proxy' => $proxy,
                'ProxyServer' => $proxyServer,
                'Share' => strtoupper($TONY_STARK['Share'] ?? 'OFF'),
                'hw_version' => "1.7-BD-" . strtoupper(substr(md5($mac), 0, 2)),
                'hw_version_2' => md5(strtolower($sn . $mac))
            ];
            @file_put_contents($DARK_SIDE . "/login.kobir", json_encode($profile, JSON_UNESCAPED_SLASHES), LOCK_EX);
            $host = parse_url($url, PHP_URL_HOST) ?: 'portal';
            @file_put_contents($DARK_SIDE . "/" . preg_replace('/[^A-Za-z0-9._-]/', '_', $host) . ".json", json_encode($profile, JSON_UNESCAPED_SLASHES), LOCK_EX);
            $GLOBALS['MJ'] = $profile;
            cleanup_session_cache($DARK_SIDE);
            echo get_profile($profile);
        } else {
            http_response_code(400);
            $reason = "Connection Failed";
            if (!$is_valid_url) $reason = "Invalid Portal URL Format";
            elseif (!$is_valid_mac) $reason = "Invalid MAC Address Structure";
            elseif (!$is_valid_sn) $reason = "Serial Number too short or missing";
            echo json_encode(["KOBIR" => ["Author" => "KOBIR_SHAH", "message" => "CHECKPOINT_ERROR", "ui_label" => $reason, "statusCode" => 400]], JSON_PRETTY_PRINT);
        }
        exit;
    }

    if ($ACTION === "all_portals") {
        $portals = [];
        foreach (glob($DARK_SIDE . "/*.json") ?: [] as $file) {
            $data = json_decode((string)@file_get_contents($file), true);
            if (is_array($data) && !empty($data['URL']) && !empty($data['MAC'])) {
                $portals[] = [
                    "id" => basename($file, ".json"),
                    "URL" => $data['URL'],
                    "MAC" => $data['MAC'],
                    "Model" => $data['Model'] ?? 'MAG254',
                    "D1" => $data['D1'] ?? '',
                    "D2" => $data['D2'] ?? '',
                    "SN" => $data['SN'] ?? '',
                    "Proxy" => $data['Proxy'] ?? 'DIRECT',
                    "ProxyServer" => $data['ProxyServer'] ?? ''
                ];
            }
        }
        echo json_encode(["status" => "success", "portals" => $portals]);
        exit;
    }

    if ($ACTION === "switch_portal") {
        $portalId = basename((string)($TONY_STARK['id'] ?? ''));
        $sourceFile = $DARK_SIDE . "/" . $portalId . ".json";
        $activeFile = $DARK_SIDE . "/login.kobir";
        if ($portalId !== '' && file_exists($sourceFile)) {
            $content = (string)@file_get_contents($sourceFile);
            if (@file_put_contents($activeFile, $content, LOCK_EX)) {
                $GLOBALS['MJ'] = json_decode($content, true);
                cleanup_session_cache($DARK_SIDE);
                echo json_encode(["statusCode" => 200, "message" => "Portal switched successfully"]);
            } else {
                echo json_encode(["statusCode" => 500, "message" => "Failed to update active session"]);
            }
        } else {
            echo json_encode(["statusCode" => 404, "message" => "Saved portal not found"]);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode(["error" => "Unknown Action Protocol"]);
    exit;
}
