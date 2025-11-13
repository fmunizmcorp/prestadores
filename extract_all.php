<?php
echo "Extracting tarballs...\n";

$files = ['config.tar.gz', 'src.tar.gz', 'public.tar.gz', 'database.tar.gz'];

foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "Extracting $file...\n";
        $phar = new PharData(__DIR__ . '/' . $file);
        $phar->extractTo(__DIR__, null, true);
        echo "✅ $file extracted\n";
        unlink(__DIR__ . '/' . $file);
    }
}

echo "\n✅ All files extracted!\n";
