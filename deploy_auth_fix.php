#!/usr/bin/env php
<?php
/**
 * Script de Deploy do AuthController Corrigido
 * Sprint 67 - Fix para skip_in_development
 */

echo "═══════════════════════════════════════════════════════\n";
echo "  SPRINT 67 - DEPLOY AUTHCONTROLLER FIX\n";
echo "═══════════════════════════════════════════════════════\n\n";

// Ler arquivo local corrigido
$localFile = __DIR__ . '/src/Controllers/AuthControllerDebug.php';
$fileContent = file_get_contents($localFile);

if ($fileContent === false) {
    die("❌ ERRO: Não foi possível ler arquivo local\n");
}

echo "✅ Arquivo local lido: " . strlen($fileContent) . " bytes\n";

// Verificar se a correção está presente
if (strpos($fileContent, "isset(\$config['recaptcha']['skip_in_development'])") !== false) {
    echo "✅ Correção verificada no arquivo\n\n";
} else {
    die("❌ ERRO: Correção não encontrada no arquivo!\n");
}

// Preparar payload para envio
$remoteUrl = "https://prestadores.clinfec.com.br/deploy_receiver.php";
$authUser = "clinfec";
$authPass = "Cf2025api#";

$postData = [
    'action' => 'update_auth_controller',
    'content' => base64_encode($fileContent),
    'auth_token' => hash('sha256', $authUser . $authPass . date('Y-m-d'))
];

echo "📤 Enviando arquivo para servidor...\n";

$ch = curl_init($remoteUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, "$authUser:$authPass");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo "❌ ERRO cURL: $curlError\n";
    die("❌ FALHA NO ENVIO\n");
}

echo "HTTP Response Code: $httpCode\n";

if ($httpCode === 404) {
    echo "\n⚠️  Endpoint de deploy não disponível via HTTP\n";
    echo "💡 Vou usar método alternativo: criar arquivo PHP temporário no servidor\n\n";
    
    // Método alternativo: criar script que se auto-atualiza via include remoto
    $deployScript = '<?php
// Auto-update script - Sprint 67
$newContent = \'' . addslashes($fileContent) . '\';
$targetFile = \'/opt/webserver/sites/prestadores/src/Controllers/AuthController.php\';

if (file_put_contents($targetFile, $newContent)) {
    echo "✅ AuthController atualizado com sucesso\\n";
    
    // Recarregar PHP-FPM
    exec("sudo systemctl reload php8.3-fpm-prestadores.service 2>&1", $output, $return);
    if ($return === 0) {
        echo "✅ PHP-FPM recarregado\\n";
    }
    
    // Limpar OPcache
    if (function_exists("opcache_reset")) {
        opcache_reset();
        echo "✅ OPcache limpo\\n";
    }
    
    echo "\\n═══ DEPLOY CONCLUÍDO ═══\\n";
    
    // Auto-deletar este script
    @unlink(__FILE__);
} else {
    echo "❌ ERRO ao atualizar arquivo\\n";
}
?>';
    
    // Salvar script localmente
    file_put_contents('/tmp/deploy_auth_sprint67.php', $deployScript);
    echo "✅ Script de deploy criado: /tmp/deploy_auth_sprint67.php\n";
    echo "\n📋 PRÓXIMOS PASSOS MANUAIS:\n";
    echo "   1. Copiar script para servidor\n";
    echo "   2. Executar: php /tmp/deploy_auth_sprint67.php\n";
    echo "   3. Verificar logs\n\n";
    
} elseif ($httpCode === 200) {
    echo "✅ UPLOAD CONCLUÍDO\n";
    echo "Response: $response\n";
} else {
    echo "❌ ERRO HTTP $httpCode\n";
    echo "Response: $response\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
