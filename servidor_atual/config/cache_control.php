<?php
/**
 * CACHE CONTROL - Desenvolvimento/Produção
 * 
 * Este arquivo controla a limpeza de cache do PHP OPcache durante o desenvolvimento.
 * 
 * USO:
 * - DESENVOLVIMENTO: Deixe as linhas ativas (sem comentário)
 * - PRODUÇÃO: Comente as linhas de opcache_reset() para melhor performance
 * 
 * INCLUIR NO INÍCIO DOS ARQUIVOS:
 * require_once __DIR__ . '/../config/cache_control.php';
 * 
 * OU (se já estiver em config/):
 * require_once __DIR__ . '/cache_control.php';
 * 
 * Criado em: 14/11/2025
 * Sprint: 33
 * Propósito: Facilitar desenvolvimento sem problemas de cache
 */

// =========================================
// CONTROLE DE CACHE - DESENVOLVIMENTO
// =========================================

/**
 * ATENÇÃO: Para PRODUÇÃO, comente as linhas abaixo
 * para melhorar a performance do sistema.
 */

// Limpar OPcache (se disponível)
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Limpar stat cache
clearstatcache(true);

// =========================================
// MODO PRODUÇÃO (COMENTAR ACIMA, DESCOMENTAR ABAIXO)
// =========================================

/**
 * Em produção, use este bloco ao invés do acima:
 * 
 * // Modo Produção - Cache habilitado para performance
 * // OPcache será gerenciado automaticamente pelo servidor
 * 
 * // Apenas limpar stat cache quando necessário
 * // clearstatcache(true);
 */

// =========================================
// INFORMAÇÕES DE DEBUG (OPCIONAL)
// =========================================

/**
 * Descomentar para ver informações de cache em desenvolvimento
 */
/*
if (function_exists('opcache_get_status')) {
    $opcache_status = opcache_get_status(false);
    if ($opcache_status) {
        error_log('[CACHE_CONTROL] OPcache Status: ' . ($opcache_status['opcache_enabled'] ? 'ENABLED' : 'DISABLED'));
    }
}
*/

// =========================================
// FIM DO ARQUIVO
// =========================================
