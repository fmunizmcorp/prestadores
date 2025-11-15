<?php
/**
 * Diagnóstico Avançado - Database.php
 * Sprint 27 - Verificar qual arquivo está sendo carregado
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico Database.php - Sprint 27</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #0a0a0a; color: #00ff00; padding: 20px; }
        .success { color: #00ff00; font-weight: bold; }
        .error { color: #ff0000; font-weight: bold; }
        .warning { color: #ffaa00; font-weight: bold; }
        .info { color: #00aaff; }
        pre { background: #1a1a1a; padding: 15px; border: 1px solid #333; border-left: 4px solid #00ff00; }
        h1 { color: #00ff00; text-shadow: 0 0 10px #00ff00; }
        h2 { color: #00aaff; border-bottom: 2px solid #00aaff; padding-bottom: 5px; }
        .box { background: #1a1a1a; border: 2px solid #333; padding: 15px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🔍 DIAGNÓSTICO AVANÇADO - DATABASE.PHP</h1>
    <p class="info">Sprint 27 - Investigação completa do problema exec()</p>
    
    <div class="box">
        <h2>1️⃣ LIMPEZA DE CACHE</h2>
        <pre><?php
        
        echo "Tentando limpar OPcache...\n";
        if (function_exists('opcache_reset')) {
            $reset = @opcache_reset();
            if ($reset) {
                echo "<span class='success'>✅ opcache_reset() executado com SUCESSO!</span>\n";
            } else {
                echo "<span class='error'>❌ opcache_reset() FALHOU</span>\n";
            }
        } else {
            echo "<span class='warning'>⚠️  opcache_reset() não disponível</span>\n";
        }
        
        echo "\nLimpando stat cache...\n";
        clearstatcache(true);
        echo "<span class='success'>✅ clearstatcache() executado</span>\n";
        
        ?></pre>
    </div>
    
    <div class="box">
        <h2>2️⃣ LOCALIZAÇÃO DO ARQUIVO Database.php</h2>
        <pre><?php
        
        // Path esperado
        $expected_path = __DIR__ . '/src/Database.php';
        echo "Path esperado: $expected_path\n";
        
        if (file_exists($expected_path)) {
            echo "<span class='success'>✅ Arquivo EXISTE</span>\n";
            echo "Tamanho: " . filesize($expected_path) . " bytes\n";
            echo "Modificado: " . date('Y-m-d H:i:s', filemtime($expected_path)) . "\n";
        } else {
            echo "<span class='error'>❌ Arquivo NÃO ENCONTRADO!</span>\n";
        }
        
        ?></pre>
    </div>
    
    <div class="box">
        <h2>3️⃣ TENTAR CARREGAR A CLASSE</h2>
        <pre><?php
        
        echo "Tentando carregar Database.php...\n";
        
        try {
            // Invalidar cache específico
            if (function_exists('opcache_invalidate')) {
                $invalidated = @opcache_invalidate($expected_path, true);
                if ($invalidated) {
                    echo "<span class='success'>✅ Cache invalidado para Database.php</span>\n";
                } else {
                    echo "<span class='warning'>⚠️  Invalidação falhou ou não necessária</span>\n";
                }
            }
            
            // Carregar arquivo
            require_once __DIR__ . '/src/Database.php';
            echo "<span class='success'>✅ Arquivo carregado com sucesso!</span>\n";
            
        } catch (Exception $e) {
            echo "<span class='error'>❌ Erro ao carregar: " . $e->getMessage() . "</span>\n";
        }
        
        ?></pre>
    </div>
    
    <div class="box">
        <h2>4️⃣ ANÁLISE DA CLASSE Database</h2>
        <pre><?php
        
        if (class_exists('App\\Database', false)) {
            echo "<span class='success'>✅ Classe App\\Database EXISTE</span>\n\n";
            
            // Usar Reflection para analisar
            $reflection = new ReflectionClass('App\\Database');
            
            echo "Arquivo: " . $reflection->getFileName() . "\n";
            echo "Namespace: " . $reflection->getNamespaceName() . "\n\n";
            
            echo "MÉTODOS PÚBLICOS:\n";
            echo str_repeat('-', 50) . "\n";
            
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            $has_exec = false;
            
            foreach ($methods as $method) {
                $name = $method->getName();
                $params = [];
                foreach ($method->getParameters() as $param) {
                    $params[] = '$' . $param->getName();
                }
                $signature = $name . '(' . implode(', ', $params) . ')';
                
                if ($name === 'exec') {
                    echo "<span class='success'>✅ ENCONTRADO: $signature</span>\n";
                    $has_exec = true;
                } else {
                    echo "   $signature\n";
                }
            }
            
            echo str_repeat('-', 50) . "\n";
            echo "Total de métodos: " . count($methods) . "\n\n";
            
            if ($has_exec) {
                echo "<span class='success'>🎉 MÉTODO exec() EXISTE NA CLASSE!</span>\n";
            } else {
                echo "<span class='error'>❌ MÉTODO exec() NÃO ENCONTRADO!</span>\n";
            }
            
        } else {
            echo "<span class='error'>❌ Classe App\\Database NÃO foi carregada!</span>\n";
        }
        
        ?></pre>
    </div>
    
    <div class="box">
        <h2>5️⃣ TESTAR CRIAÇÃO DE INSTÂNCIA</h2>
        <pre><?php
        
        if (class_exists('App\\Database')) {
            echo "Tentando criar instância...\n";
            
            try {
                // Não vamos realmente criar porque precisa do banco
                // Apenas verificar se conseguimos acessar os métodos
                $methods = get_class_methods('App\\Database');
                
                echo "\nMétodos disponíveis via get_class_methods():\n";
                echo str_repeat('-', 50) . "\n";
                
                foreach ($methods as $method) {
                    if ($method === 'exec') {
                        echo "<span class='success'>✅ $method</span>\n";
                    } else {
                        echo "   $method\n";
                    }
                }
                
                if (in_array('exec', $methods)) {
                    echo "\n<span class='success'>🎉 CONFIRMADO: exec() está disponível!</span>\n";
                } else {
                    echo "\n<span class='error'>❌ exec() NÃO está na lista!</span>\n";
                }
                
            } catch (Exception $e) {
                echo "<span class='error'>❌ Erro: " . $e->getMessage() . "</span>\n";
            }
        }
        
        ?></pre>
    </div>
    
    <div class="box">
        <h2>6️⃣ VERIFICAR CONTEÚDO DO ARQUIVO</h2>
        <pre><?php
        
        if (file_exists($expected_path)) {
            $content = file_get_contents($expected_path);
            
            echo "Tamanho total: " . strlen($content) . " bytes\n";
            echo "Linhas: " . substr_count($content, "\n") . "\n\n";
            
            // Procurar por método exec
            if (strpos($content, 'public function exec') !== false) {
                echo "<span class='success'>✅ String 'public function exec' ENCONTRADA no arquivo!</span>\n";
                
                // Extrair a linha
                $lines = explode("\n", $content);
                foreach ($lines as $num => $line) {
                    if (stripos($line, 'public function exec') !== false) {
                        echo "\nLinha " . ($num + 1) . ":\n";
                        echo "<span class='info'>" . htmlspecialchars($line) . "</span>\n";
                        
                        // Mostrar contexto (3 linhas antes e depois)
                        echo "\nContexto:\n";
                        for ($i = max(0, $num - 2); $i <= min(count($lines) - 1, $num + 3); $i++) {
                            $marker = ($i === $num) ? '>>> ' : '    ';
                            echo $marker . htmlspecialchars($lines[$i]) . "\n";
                        }
                    }
                }
            } else {
                echo "<span class='error'>❌ String 'public function exec' NÃO encontrada no arquivo!</span>\n";
            }
            
            // Procurar por outros métodos proxy
            $proxy_methods = ['query', 'prepare', 'beginTransaction', 'commit', 'rollBack'];
            echo "\n\nOutros métodos proxy:\n";
            foreach ($proxy_methods as $method) {
                $found = strpos($content, "public function $method") !== false;
                if ($found) {
                    echo "<span class='success'>✅ $method()</span>\n";
                } else {
                    echo "<span class='error'>❌ $method()</span>\n";
                }
            }
        }
        
        ?></pre>
    </div>
    
    <div class="box">
        <h2>7️⃣ STATUS DO OPCACHE</h2>
        <pre><?php
        
        if (function_exists('opcache_get_status')) {
            $status = @opcache_get_status(false);
            if ($status) {
                echo "<span class='success'>✅ OPcache ATIVO</span>\n\n";
                echo "Configuração:\n";
                echo "  revalidate_freq: " . ini_get('opcache.revalidate_freq') . " segundos\n";
                echo "  validate_timestamps: " . (ini_get('opcache.validate_timestamps') ? 'Sim' : 'Não') . "\n";
                echo "  consistency_checks: " . (ini_get('opcache.consistency_checks') ? 'Sim' : 'Não') . "\n\n";
                
                echo "Estatísticas:\n";
                echo "  Scripts em cache: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
                echo "  Hits: " . number_format($status['opcache_statistics']['hits']) . "\n";
                echo "  Misses: " . number_format($status['opcache_statistics']['misses']) . "\n";
                echo "  Hit rate: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
            } else {
                echo "<span class='warning'>⚠️  Status do OPcache não disponível</span>\n";
            }
        } else {
            echo "<span class='warning'>⚠️  Função opcache_get_status() não disponível</span>\n";
        }
        
        ?></pre>
    </div>
    
    <div class="box">
        <h2>📊 CONCLUSÃO</h2>
        <pre><?php
        
        echo "RESUMO DO DIAGNÓSTICO:\n";
        echo str_repeat('=', 50) . "\n\n";
        
        $class_exists = class_exists('App\\Database', false);
        $has_exec = false;
        
        if ($class_exists) {
            $methods = get_class_methods('App\\Database');
            $has_exec = in_array('exec', $methods);
        }
        
        if ($class_exists && $has_exec) {
            echo "<span class='success'>🎉 SUCESSO TOTAL!</span>\n\n";
            echo "✅ Classe Database carregada\n";
            echo "✅ Método exec() disponível\n";
            echo "✅ Sistema deve funcionar agora\n\n";
            echo "PRÓXIMO PASSO: Testar https://prestadores.clinfec.com.br/\n";
        } elseif ($class_exists && !$has_exec) {
            echo "<span class='error'>❌ PROBLEMA CONFIRMADO!</span>\n\n";
            echo "✅ Classe Database carregada\n";
            echo "❌ Método exec() AUSENTE\n";
            echo "❌ Arquivo no disco tem exec(), mas classe carregada não\n\n";
            echo "CAUSA: Cache intermediário ou arquivo errado sendo carregado\n";
        } else {
            echo "<span class='error'>❌ ERRO DE CARREGAMENTO!</span>\n\n";
            echo "❌ Classe Database não foi carregada\n";
            echo "❌ Verificar autoload e namespaces\n";
        }
        
        ?></pre>
    </div>
    
    <hr style="border-color: #333; margin: 30px 0;">
    
    <p style="text-align: center;">
        <a href="/" style="font-size: 20px; color: #00ff00; background: #003300; padding: 15px 30px; text-decoration: none; border: 2px solid #00ff00; display: inline-block;">
            ▶ TESTAR SISTEMA PRINCIPAL
        </a>
    </p>
    
</body>
</html>
