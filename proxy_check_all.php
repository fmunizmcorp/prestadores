<?php
// Proxy to execute check_all_tables.php from GitHub
header('Content-Type: text/plain; charset=utf-8');
$url = 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/769af53/check_all_tables.php';
$code = file_get_contents($url);
if ($code) {
    eval('?>' . $code);
} else {
    echo "Failed to fetch from GitHub\n";
}
