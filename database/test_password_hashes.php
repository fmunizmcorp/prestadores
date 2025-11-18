<?php
/**
 * Sprint 67 - Test password hashes
 * Verifica se os hashes usados no Sprint 66 estão corretos
 */

echo "==============================================\n";
echo "SPRINT 67 - TESTE DE HASHES DE SENHA\n";
echo "==============================================\n\n";

// Hashes do Sprint 66
$hash_password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
$hash_admin123 = '$2y$10$VJL2WmMq9Kh7FHPqYG8P2.Y8ZHPqT5xQwE0pXk7nOmKm3F9F/R5Wa';

echo "1. Testando hash para 'password':\n";
echo "   Hash: $hash_password\n";
$result_password = password_verify('password', $hash_password);
echo "   Verifica 'password': " . ($result_password ? "✅ OK" : "❌ FALHA") . "\n\n";

echo "2. Testando hash para 'admin123':\n";
echo "   Hash: $hash_admin123\n";
$result_admin123 = password_verify('admin123', $hash_admin123);
echo "   Verifica 'admin123': " . ($result_admin123 ? "✅ OK" : "❌ FALHA") . "\n\n";

echo "==============================================\n";
echo "GERANDO NOVOS HASHES (se necessário)\n";
echo "==============================================\n\n";

// Se algum hash falhou, gerar novos
if (!$result_password) {
    $new_hash_password = password_hash('password', PASSWORD_DEFAULT);
    echo "NOVO hash para 'password':\n";
    echo "$new_hash_password\n\n";
} else {
    echo "Hash 'password' OK - Não precisa trocar\n\n";
}

if (!$result_admin123) {
    $new_hash_admin123 = password_hash('admin123', PASSWORD_DEFAULT);
    echo "NOVO hash para 'admin123':\n";
    echo "$new_hash_admin123\n\n";
} else {
    echo "Hash 'admin123' OK - Não precisa trocar\n\n";
}

echo "==============================================\n";
echo "CONCLUSÃO\n";
echo "==============================================\n";

if ($result_password && $result_admin123) {
    echo "✅ Todos os hashes estão CORRETOS!\n";
    echo "   O problema NÃO está nos hashes de senha.\n";
} else {
    echo "❌ Alguns hashes estão INCORRETOS!\n";
    echo "   Use os novos hashes gerados acima.\n";
}

echo "\n";
