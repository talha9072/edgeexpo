<?php
// Local helper: find every image of the old site that the Wayback Machine still holds.
// usage: http://edgeexpo.local/wp-content/themes/edgeexpo/dump/tools/wayback.php?step=list
//        ?step=fetch&from=0&count=25   downloads the list into dump/wayback/ (skips files already there)
require dirname(__DIR__, 5) . '/wp-load.php';

if (! str_ends_with((string) wp_parse_url(home_url(), PHP_URL_HOST), '.local')) {
    exit('local only');
}
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(600);

$dir  = dirname(__DIR__) . '/wayback';
$list = $dir . '/list.json';
wp_mkdir_p($dir);

$step = $_GET['step'] ?? 'list';

if ('list' === $step) {
    $hosts = array('edgeexpo.co.za', 'www.edgeexpo.co.za', 'edgeexpos.co.za', 'www.edgeexpos.co.za');
    $found = array();
    foreach ($hosts as $host) {
        $api  = 'https://web.archive.org/cdx/search/cdx?url=' . rawurlencode($host . '/wp-content/uploads/') . '&matchType=prefix&output=json&fl=timestamp,original,mimetype,statuscode,length&filter=statuscode:200&limit=20000';
        $resp = wp_remote_get($api, array('timeout' => 120));
        if (is_wp_error($resp)) {
            echo "{$host}: ERROR ", $resp->get_error_message(), "\n";
            continue;
        }
        $rows = json_decode(wp_remote_retrieve_body($resp), true) ?: array();
        array_shift($rows);
        echo "{$host}: ", count($rows), " captures (HTTP ", wp_remote_retrieve_response_code($resp), ")\n";
        foreach ($rows as $r) {
            [$ts, $orig, $mime, , $len] = $r;
            if (! str_starts_with($mime, 'image/') && ! preg_match('/\.(jpe?g|png|gif|webp)$/i', $orig)) {
                continue;
            }
            $path = preg_replace('#^.*?/wp-content/uploads/#', '', strtok($orig, '?'));
            $path = rawurldecode($path);
            // Keep the newest capture of each file.
            if (! isset($found[$path]) || $ts > $found[$path]['ts']) {
                $found[$path] = array('ts' => $ts, 'orig' => $orig, 'len' => (int) $len);
            }
        }
    }
    ksort($found);
    file_put_contents($list, wp_json_encode($found, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo 'unique files: ', count($found), "\n";
    foreach ($found as $path => $f) {
        echo $f['ts'], ' ', $path, "\n";
    }
    exit;
}

if ('fetch' === $step) {
    $found = json_decode((string) file_get_contents($list), true) ?: array();
    $from  = (int) ($_GET['from'] ?? 0);
    $count = (int) ($_GET['count'] ?? 25);
    $keys  = array_slice(array_keys($found), $from, $count);
    foreach ($keys as $path) {
        $f    = $found[$path];
        $dest = $dir . '/files/' . $path;
        if (file_exists($dest) && filesize($dest) > 0) {
            echo "have {$path}\n";
            continue;
        }
        wp_mkdir_p(dirname($dest));
        $resp = wp_remote_get('https://web.archive.org/web/' . $f['ts'] . 'id_/' . $f['orig'], array('timeout' => 60));
        $code = is_wp_error($resp) ? $resp->get_error_message() : wp_remote_retrieve_response_code($resp);
        $type = is_wp_error($resp) ? '' : (string) wp_remote_retrieve_header($resp, 'content-type');
        if (200 === $code && str_starts_with($type, 'image/')) {
            file_put_contents($dest, wp_remote_retrieve_body($resp));
            $size = @getimagesize($dest);
            echo "ok {$path} ", filesize($dest), ' bytes ', $size ? $size[0] . 'x' . $size[1] : '?', "\n";
        } else {
            echo "FAIL {$path} {$code} {$type}\n";
        }
    }
    echo 'done ', $from, '-', $from + count($keys), ' of ', count($found), "\n";
}
