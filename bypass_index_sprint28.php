<?php
/**
 * BYPASS INDEX - Sprint 28 V2
 * Este arquivo bypassa completamente o OPcache
 * Usa arquivo com nome único que não está em cache!
 */

// Include the index with unique name (not cached)
require_once __DIR__ . '/public/index_v17_sprint28.php';
