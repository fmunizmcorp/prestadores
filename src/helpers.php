<?php /* Cache-Buster: 2025-11-15 12:18:13 */ 
/**
 * Funções auxiliares globais
 */

/**
 * Sanitiza entrada de dados
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redireciona para uma URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Retorna a URL base da aplicação
 */
function base_url($path = '') {
    $config = require __DIR__ . '/../config/app.php';
    return rtrim($config['url'], '/') . '/' . ltrim($path, '/');
}

/**
 * Retorna a URL de assets
 */
function asset($path) {
    return base_url('public/' . ltrim($path, '/'));
}

/**
 * Define uma mensagem flash na sessão
 */
function flash($key, $message = null) {
    if ($message === null) {
        $value = $_SESSION["flash_$key"] ?? null;
        unset($_SESSION["flash_$key"]);
        return $value;
    }
    $_SESSION["flash_$key"] = $message;
}

/**
 * Verifica se o usuário está autenticado
 */
function is_authenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Retorna o usuário atual
 */
function current_user() {
    if (!is_authenticated()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'nome' => $_SESSION['user_nome'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
        'role_level' => $_SESSION['user_role_level'] ?? 0
    ];
}

/**
 * Verifica se o usuário tem uma determinada permissão
 */
function has_permission($required_level) {
    $user = current_user();
    if (!$user) {
        return false;
    }
    return $user['role_level'] >= $required_level;
}

/**
 * Gera um token CSRF
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida um token CSRF
 */
function csrf_validate($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Formata data brasileira
 */
function format_date($date, $format = 'd/m/Y') {
    if (!$date) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formata data e hora brasileira
 */
function format_datetime($datetime, $format = 'd/m/Y H:i') {
    if (!$datetime) return '';
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    return date($format, $timestamp);
}

/**
 * Formata CNPJ
 */
function format_cnpj($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
}

/**
 * Formata telefone
 */
function format_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) == 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone);
    }
    return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone);
}

/**
 * Valida email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida CNPJ
 */
function validate_cnpj($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    
    if (strlen($cnpj) != 14) return false;
    
    if (preg_match('/(\d)\1{13}/', $cnpj)) return false;
    
    // Validação do primeiro dígito
    $sum = 0;
    $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    for ($i = 0; $i < 12; $i++) {
        $sum += $cnpj[$i] * $weights[$i];
    }
    $digit1 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);
    
    if ($cnpj[12] != $digit1) return false;
    
    // Validação do segundo dígito
    $sum = 0;
    $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    for ($i = 0; $i < 13; $i++) {
        $sum += $cnpj[$i] * $weights[$i];
    }
    $digit2 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);
    
    return $cnpj[13] == $digit2;
}

/**
 * Gera log de atividades
 */
function log_activity($action, $details = null) {
    $logFile = __DIR__ . '/../logs/activity_' . date('Y-m-d') . '.log';
    $user = current_user();
    $userId = $user ? $user['id'] : 'guest';
    $userName = $user ? $user['nome'] : 'Visitante';
    
    $logMessage = sprintf(
        "[%s] User: %s (ID: %s) | Action: %s | IP: %s | Details: %s\n",
        date('Y-m-d H:i:s'),
        $userName,
        $userId,
        $action,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $details ? json_encode($details) : 'none'
    );
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
