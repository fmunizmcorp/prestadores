<?php

namespace App\Models;

use App\Database;
use PDO;

/**
 * Model SystemSetting - Gerenciamento de Configurações do Sistema
 * Sprint 65 - SMTP e Configurações Gerais
 */
class SystemSetting extends BaseModel
{
    protected static $table = 'system_settings';
    
    /**
     * Chave de criptografia simples (em produção, usar env var)
     */
    private const ENCRYPTION_KEY = 'ClinfecPrestadores2025SecretKey';
    
    /**
     * Obter valor de uma configuração
     * 
     * @param string $key Chave da configuração
     * @param mixed $default Valor padrão se não existir
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT setting_value, setting_type, is_encrypted FROM " . static::$table . " WHERE setting_key = ?");
        $stmt->execute([$key]);
        $setting = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$setting) {
            return $default;
        }
        
        $value = $setting['setting_value'];
        
        // Descriptografar se necessário
        if ($setting['is_encrypted'] == 1 && !empty($value)) {
            $value = self::decrypt($value);
        }
        
        // Converter tipo
        return self::convertType($value, $setting['setting_type']);
    }
    
    /**
     * Definir valor de uma configuração
     * 
     * @param string $key Chave da configuração
     * @param mixed $value Valor
     * @param bool $encrypt Criptografar valor
     * @return bool
     */
    public static function set(string $key, $value, bool $encrypt = false): bool
    {
        $db = Database::getInstance();
        
        // Criptografar se necessário
        if ($encrypt && !empty($value)) {
            $value = self::encrypt($value);
        }
        
        // Verificar se já existe
        $stmt = $db->prepare("SELECT id FROM " . static::$table . " WHERE setting_key = ?");
        $stmt->execute([$key]);
        
        if ($stmt->fetch()) {
            // Atualizar
            $stmt = $db->prepare("UPDATE " . static::$table . " SET setting_value = ?, is_encrypted = ?, updated_at = NOW() WHERE setting_key = ?");
            return $stmt->execute([$value, $encrypt ? 1 : 0, $key]);
        } else {
            // Inserir
            $stmt = $db->prepare("INSERT INTO " . static::$table . " (setting_key, setting_value, is_encrypted) VALUES (?, ?, ?)");
            return $stmt->execute([$key, $value, $encrypt ? 1 : 0]);
        }
    }
    
    /**
     * Obter todas as configurações de uma categoria
     * 
     * @param string $category Categoria
     * @return array
     */
    public static function getByCategory(string $category): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE category = ? ORDER BY setting_key");
        $stmt->execute([$category]);
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($settings as $setting) {
            $value = $setting['setting_value'];
            
            // Descriptografar se necessário
            if ($setting['is_encrypted'] == 1 && !empty($value)) {
                $value = self::decrypt($value);
            }
            
            $result[$setting['setting_key']] = [
                'value' => self::convertType($value, $setting['setting_type']),
                'type' => $setting['setting_type'],
                'description' => $setting['description'],
                'encrypted' => $setting['is_encrypted']
            ];
        }
        
        return $result;
    }
    
    /**
     * Obter configurações SMTP
     * 
     * @return array
     */
    public static function getSmtpConfig(): array
    {
        $settings = self::getByCategory('email');
        
        return [
            'host' => $settings['smtp_host']['value'] ?? 'localhost',
            'port' => (int)($settings['smtp_port']['value'] ?? 587),
            'secure' => $settings['smtp_secure']['value'] ?? 'tls',
            'username' => $settings['smtp_username']['value'] ?? '',
            'password' => $settings['smtp_password']['value'] ?? '',
            'from_email' => $settings['smtp_from_email']['value'] ?? 'noreply@clinfec.com.br',
            'from_name' => $settings['smtp_from_name']['value'] ?? 'Sistema Clinfec',
            'enabled' => (bool)($settings['smtp_enabled']['value'] ?? true),
            'driver' => $settings['mail_driver']['value'] ?? 'smtp'
        ];
    }
    
    /**
     * Salvar configurações SMTP
     * 
     * @param array $config Configurações
     * @return bool
     */
    public static function saveSmtpConfig(array $config): bool
    {
        $success = true;
        
        if (isset($config['smtp_host'])) {
            $success = $success && self::set('smtp_host', $config['smtp_host']);
        }
        
        if (isset($config['smtp_port'])) {
            $success = $success && self::set('smtp_port', $config['smtp_port']);
        }
        
        if (isset($config['smtp_secure'])) {
            $success = $success && self::set('smtp_secure', $config['smtp_secure']);
        }
        
        if (isset($config['smtp_username'])) {
            $success = $success && self::set('smtp_username', $config['smtp_username']);
        }
        
        if (isset($config['smtp_password']) && !empty($config['smtp_password'])) {
            $success = $success && self::set('smtp_password', $config['smtp_password'], true);
        }
        
        if (isset($config['smtp_from_email'])) {
            $success = $success && self::set('smtp_from_email', $config['smtp_from_email']);
        }
        
        if (isset($config['smtp_from_name'])) {
            $success = $success && self::set('smtp_from_name', $config['smtp_from_name']);
        }
        
        if (isset($config['smtp_enabled'])) {
            $success = $success && self::set('smtp_enabled', $config['smtp_enabled'] ? '1' : '0');
        }
        
        return $success;
    }
    
    /**
     * Converter tipo de valor
     * 
     * @param mixed $value Valor
     * @param string $type Tipo
     * @return mixed
     */
    private static function convertType($value, string $type)
    {
        switch ($type) {
            case 'integer':
                return (int)$value;
            case 'boolean':
                return (bool)$value || $value === '1' || $value === 'true';
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
    
    /**
     * Criptografar valor
     * 
     * @param string $value Valor
     * @return string
     */
    private static function encrypt(string $value): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($value, 'aes-256-cbc', self::ENCRYPTION_KEY, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    /**
     * Descriptografar valor
     * 
     * @param string $value Valor criptografado
     * @return string
     */
    private static function decrypt(string $value): string
    {
        $parts = explode('::', base64_decode($value), 2);
        if (count($parts) !== 2) {
            return '';
        }
        
        list($encrypted_data, $iv) = $parts;
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', self::ENCRYPTION_KEY, 0, $iv);
    }
    
    /**
     * Deletar uma configuração
     * 
     * @param string $key Chave
     * @return bool
     */
    public static function delete(string $key): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM " . static::$table . " WHERE setting_key = ?");
        return $stmt->execute([$key]);
    }
    
    /**
     * Obter todas as categorias disponíveis
     * 
     * @return array
     */
    public static function getCategories(): array
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT DISTINCT category FROM " . static::$table . " ORDER BY category");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
