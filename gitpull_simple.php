<?php
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(300);

echo "═══════════════════════════════════════════════════════════\n";
echo "GIT PULL DEPLOYMENT\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "Current Dir: " . getcwd() . "\n\n";

// Check if .git exists
if (is_dir('.git')) {
    echo "✓ Git repository detected\n\n";
    
    echo "[1/4] Git fetch...\n";
    exec("git fetch origin genspark_ai_developer 2>&1", $out1, $ret1);
    if ($ret1 === 0) {
        echo "✓ Fetch complete\n\n";
    } else {
        echo "Output:\n" . implode("\n", $out1) . "\n\n";
    }
    
    echo "[2/4] Creating backup...\n";
    $backup = 'backup_' . date('YmdHis') . '.tar.gz';
    exec("tar -czf $backup src/Models/ 2>&1", $out2, $ret2);
    echo ($ret2 === 0 ? "✓" : "⚠") . " Backup: $backup\n\n";
    
    echo "[3/4] Git reset to latest...\n";
    exec("git reset --hard origin/genspark_ai_developer 2>&1", $out3, $ret3);
    if ($ret3 === 0) {
        echo "✓ Reset complete\n";
        echo "Latest commit deployed!\n\n";
    } else {
        echo "Output:\n" . implode("\n", $out3) . "\n\n";
    }
    
    echo "[4/4] Permissions...\n";
    exec("chmod -R 755 . 2>&1");
    exec("chmod -R 777 public/uploads 2>&1");
    echo "✓ Done\n\n";
    
    echo "═══════════════════════════════════════════════════════════\n";
    echo "✅ DEPLOYMENT COMPLETE!\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    echo "Clear cache: https://prestadores.clinfec.com.br/clear_cache.php\n";
    echo "Test: https://prestadores.clinfec.com.br/projetos\n";
} else {
    echo "✗ Not a git repository\n";
    echo "Cannot use git pull method\n";
}
