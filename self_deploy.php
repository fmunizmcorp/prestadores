<?php
// AUTO-DEPLOY SCRIPT - Execute via: curl https://...com.br/THISFILE.php
error_reporting(0);
@ini_set('display_errors', 0);

// Files to deploy
\$files = [
    'public/index.php' => 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/genspark_ai_developer/public/index.php',
    'src/Views/dashboard/index.php' => 'https://raw.githubusercontent.com/fmunizmcorp/prestadores/genspark_ai_developer/src/Views/dashboard/index.php'
];

foreach (\$files as \$path => \$url) {
    \$content = @file_get_contents(\$url);
    if (\$content) {
        \$target = __DIR__ . '/' . \$path;
        @mkdir(dirname(\$target), 0755, true);
        @file_put_contents(\$target, \$content);
    }
}

echo "OK";
