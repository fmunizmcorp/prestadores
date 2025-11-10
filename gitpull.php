<?php
header('Content-Type: text/plain; charset=utf-8');
echo "═══════════════════════════════════════════════════════════\n";
echo "GIT PULL DEPLOYER\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "Current Dir: " . getcwd() . "\n\n";

// Check if git exists
exec("which git 2>&1", $output1, $ret1);
if ($ret1 === 0) {
    echo "✓ Git found: " . trim($output1[0]) . "\n\n";
} else {
    echo "✗ Git not available\n";
    exit(1);
}

// Check if this is a git repo
if (is_dir('.git')) {
    echo "✓ Git repository detected\n\n";
} else {
    echo "⚠ Not a git repository\n";
    echo "Initializing...\n";
    exec("git init 2>&1", $output2, $ret2);
    exec("git remote add origin https://github.com/fmunizmcorp/prestadores.git 2>&1");
}

// Fetch latest
echo "Fetching latest code...\n";
exec("git fetch origin genspark_ai_developer 2>&1", $output3, $ret3);

if ($ret3 === 0) {
    echo "✓ Fetch successful\n\n";
    
    // Reset to latest
    echo "Resetting to latest commit...\n";
    exec("git reset --hard origin/genspark_ai_developer 2>&1", $output4, $ret4);
    
    if ($ret4 === 0) {
        echo "✓ Reset successful!\n\n";
        
        // Set permissions
        echo "Setting permissions...\n";
        exec("chmod -R 755 . 2>&1");
        exec("chmod -R 777 public/uploads 2>&1");
        echo "✓ Done!\n\n";
        
        echo "═══════════════════════════════════════════════════════════\n";
        echo "✅ DEPLOYMENT COMPLETE!\n";
        echo "═══════════════════════════════════════════════════════════\n";
    } else {
        echo "✗ Reset failed\n";
        print_r($output4);
    }
} else {
    echo "✗ Fetch failed\n";
    print_r($output3);
}
