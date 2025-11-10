<?php
/**
 * GIT PULL TRIGGER
 * Pulls latest code from GitHub main branch
 */
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(300);

echo "═══════════════════════════════════════════════════════════\n";
echo "GIT PULL FROM GITHUB MAIN\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$base_path = getcwd();
echo "Working directory: $base_path\n\n";

// Check if .git exists
if (!is_dir('.git')) {
    echo "❌ Not a git repository!\n";
    echo "Location: $base_path\n";
    exit(1);
}

echo "✅ Git repository detected\n\n";

// Check current branch
echo "[1/4] Checking current branch...\n";
$branch = trim(shell_exec('git branch --show-current 2>&1'));
echo "Current branch: $branch\n\n";

// Fetch latest
echo "[2/4] Fetching from origin...\n";
$output = shell_exec('git fetch origin main 2>&1');
echo $output . "\n";

// Check if there are changes
echo "[3/4] Checking for updates...\n";
$status = shell_exec('git status -uno 2>&1');
echo $status . "\n";

// Pull
echo "[4/4] Pulling latest code...\n";
$pull_output = shell_exec('git pull origin main 2>&1');
echo $pull_output . "\n";

echo "═══════════════════════════════════════════════════════════\n";
echo "✅ GIT PULL COMPLETE\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "Next steps:\n";
echo "1. Clear OPcache: access clear_cache.php\n";
echo "2. Test routes: run test_all_routes.sh\n";
