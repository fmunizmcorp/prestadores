<?php
// BOOTSTRAP DEPLOYER
header('Content-Type: text/plain');
$url = 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/genspark_ai_developer/autodeploy.php';
echo "Fetching deployer from GitHub...\n";
$code = file_get_contents($url);
if ($code) {
    echo "Executing...\n\n";
    eval('?>' . $code);
} else {
    echo "Failed to fetch!\n";
}
