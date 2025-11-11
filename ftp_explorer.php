<?php
/**
 * FTP EXPLORER - Discover complete FTP structure
 */

$ftp_server = "ftp.clinfec.com.br";
$ftp_user = "u673902663.genspark1";
$ftp_pass = "Genspark1@";

echo "=== FTP EXPLORER ===\n\n";

$conn = ftp_connect($ftp_server);
if (!$conn) {
    die("ERROR: Could not connect to FTP server\n");
}

echo "✅ Connected to $ftp_server\n";

$login = ftp_login($conn, $ftp_user, $ftp_pass);
if (!$login) {
    die("ERROR: Could not login\n");
}

echo "✅ Logged in as $ftp_user\n\n";

ftp_pasv($conn, true);

// Get current directory
$pwd = ftp_pwd($conn);
echo "Current Directory: $pwd\n\n";

// Function to list directory recursively
function list_dir($conn, $dir, $depth = 0, $max_depth = 3) {
    if ($depth > $max_depth) return;
    
    $list = ftp_nlist($conn, $dir);
    if (!$list) return;
    
    foreach ($list as $item) {
        $basename = basename($item);
        
        // Skip hidden files and parent references
        if ($basename == '.' || $basename == '..' || $basename[0] == '.') {
            continue;
        }
        
        $indent = str_repeat("  ", $depth);
        
        // Try to change to directory to check if it's a directory
        if (@ftp_chdir($conn, $item)) {
            echo $indent . "📁 $basename/\n";
            
            // Check if this is the prestadores directory
            if (strpos($basename, 'prestadores') !== false) {
                echo $indent . "   ⭐ FOUND PRESTADORES!\n";
                echo $indent . "   Full path: $item\n";
                
                // List contents
                $contents = ftp_nlist($conn, $item);
                if ($contents) {
                    foreach ($contents as $file) {
                        echo $indent . "     - " . basename($file) . "\n";
                    }
                }
            }
            
            ftp_chdir($conn, $dir); // Go back
            
            // Recurse into subdirectory
            if ($depth < $max_depth) {
                list_dir($conn, $item, $depth + 1, $max_depth);
            }
        } else {
            echo $indent . "📄 $basename\n";
        }
    }
}

echo "=== DIRECTORY STRUCTURE ===\n\n";
list_dir($conn, $pwd);

echo "\n=== SEARCHING FOR PRESTADORES ===\n\n";

// Try common paths
$possible_paths = [
    '/prestadores',
    '/public_html/prestadores',
    '/domains/clinfec.com.br/public_html/prestadores',
    'prestadores',
    'public_html/prestadores'
];

foreach ($possible_paths as $path) {
    echo "Trying: $path ... ";
    if (@ftp_chdir($conn, $path)) {
        echo "✅ FOUND!\n";
        $full_path = ftp_pwd($conn);
        echo "Full path: $full_path\n";
        
        // List contents
        $contents = ftp_nlist($conn, '.');
        if ($contents) {
            echo "Contents:\n";
            foreach ($contents as $file) {
                echo "  - " . basename($file) . "\n";
            }
        }
        break;
    } else {
        echo "❌ Not found\n";
    }
}

ftp_close($conn);
echo "\n=== EXPLORATION COMPLETE ===\n";
