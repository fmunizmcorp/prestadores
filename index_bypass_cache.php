<?php
/**
 * INDEX WRAPPER - Sprint 30 BYPASS CACHE
 * 
 * Problema: PHP 8.1 mantém versão antiga de index.php em cache mesmo após:
 * - opcache desabilitado
 * - realpath_cache=0
 * - restart PHP completo
 * 
 * Solução: Chamar arquivo com nome ÚNICO que cache não conhece
 */

require_once __DIR__ . '/public/index_unique_1763123588.php';
