<?php

namespace App\Services;

use App\Models\SystemSetting;

/**
 * EmailService - Serviço de Envio de Emails
 * Sprint 65 - SMTP Configuration
 * 
 * Suporta SMTP com configurações do banco de dados
 */
class EmailService
{
    private $config;
    
    /**
     * Construtor
     */
    public function __construct()
    {
        $this->config = SystemSetting::getSmtpConfig();
    }
    
    /**
     * Enviar email
     * 
     * @param string $to Email destinatário
     * @param string $subject Assunto
     * @param string $body Corpo do email (HTML)
     * @param string $altBody Corpo alternativo (texto puro)
     * @return bool
     */
    public function send(string $to, string $subject, string $body, string $altBody = ''): bool
    {
        if (!$this->config['enabled']) {
            error_log('[EmailService] SMTP está desabilitado nas configurações');
            return false;
        }
        
        // Usar PHPMailer se disponível, senão usar mail() nativo
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            return $this->sendWithPHPMailer($to, $subject, $body, $altBody);
        } else {
            return $this->sendWithNativeMail($to, $subject, $body);
        }
    }
    
    /**
     * Enviar email com PHPMailer
     * 
     * @param string $to Destinatário
     * @param string $subject Assunto
     * @param string $body Corpo HTML
     * @param string $altBody Corpo texto
     * @return bool
     */
    private function sendWithPHPMailer(string $to, string $subject, string $body, string $altBody = ''): bool
    {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configurações SMTP
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->Port = $this->config['port'];
            $mail->SMTPAuth = !empty($this->config['username']);
            
            if (!empty($this->config['username'])) {
                $mail->Username = $this->config['username'];
                $mail->Password = $this->config['password'];
            }
            
            // Segurança
            if ($this->config['secure'] === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($this->config['secure'] === 'tls') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            // Remetente e destinatário
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($to);
            
            // Conteúdo
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody ?: strip_tags($body);
            
            return $mail->send();
            
        } catch (\Exception $e) {
            error_log('[EmailService] Erro PHPMailer: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar email com mail() nativo
     * 
     * @param string $to Destinatário
     * @param string $subject Assunto
     * @param string $body Corpo
     * @return bool
     */
    private function sendWithNativeMail(string $to, string $subject, string $body): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->config['from_name'] . ' <' . $this->config['from_email'] . '>',
            'Reply-To: ' . $this->config['from_email'],
            'X-Mailer: PHP/' . phpversion()
        ];
        
        return mail($to, $subject, $body, implode("\r\n", $headers));
    }
    
    /**
     * Enviar email de teste
     * 
     * @param string $to Destinatário
     * @return bool
     */
    public function sendTestEmail(string $to): bool
    {
        $subject = 'Teste de Email - Sistema Clinfec';
        
        $body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>✉️ Email de Teste</h1>
                </div>
                <div class="content">
                    <h2>Configuração SMTP Funcionando!</h2>
                    <p>Este é um email de teste do <strong>Sistema de Gestão de Prestadores - Clinfec</strong>.</p>
                    <p>Se você recebeu este email, significa que as configurações SMTP estão corretas e funcionando perfeitamente.</p>
                    <hr>
                    <p><strong>Configurações utilizadas:</strong></p>
                    <ul>
                        <li>Servidor: ' . htmlspecialchars($this->config['host']) . '</li>
                        <li>Porta: ' . htmlspecialchars($this->config['port']) . '</li>
                        <li>Segurança: ' . htmlspecialchars(strtoupper($this->config['secure'])) . '</li>
                        <li>Remetente: ' . htmlspecialchars($this->config['from_email']) . '</li>
                    </ul>
                </div>
                <div class="footer">
                    <p>Sistema Clinfec - ' . date('Y') . '</p>
                    <p>Data/Hora: ' . date('d/m/Y H:i:s') . '</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $altBody = "Email de Teste - Sistema Clinfec\n\n" .
                   "Este é um email de teste. Se você recebeu, as configurações SMTP estão corretas!\n\n" .
                   "Servidor: {$this->config['host']}\n" .
                   "Porta: {$this->config['port']}\n" .
                   "Data: " . date('d/m/Y H:i:s');
        
        return $this->send($to, $subject, $body, $altBody);
    }
    
    /**
     * Enviar email de recuperação de senha
     * 
     * @param string $to Email do usuário
     * @param string $nome Nome do usuário
     * @param string $token Token de recuperação
     * @return bool
     */
    public function sendPasswordReset(string $to, string $nome, string $token): bool
    {
        $resetLink = defined('BASE_URL') ? BASE_URL . '/?page=auth&action=resetPassword&token=' . $token : '#';
        
        $subject = 'Recuperação de Senha - Sistema Clinfec';
        
        $body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .button { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔐 Recuperação de Senha</h1>
                </div>
                <div class="content">
                    <p>Olá, <strong>' . htmlspecialchars($nome) . '</strong>!</p>
                    <p>Recebemos uma solicitação de recuperação de senha para sua conta no Sistema Clinfec.</p>
                    <p>Para redefinir sua senha, clique no botão abaixo:</p>
                    <p style="text-align: center;">
                        <a href="' . htmlspecialchars($resetLink) . '" class="button">Redefinir Senha</a>
                    </p>
                    <p>Ou copie e cole o link abaixo no seu navegador:</p>
                    <p style="word-break: break-all; background: white; padding: 10px; border-radius: 4px;">
                        ' . htmlspecialchars($resetLink) . '
                    </p>
                    <p><strong>Este link expira em 1 hora.</strong></p>
                    <hr>
                    <p style="font-size: 12px; color: #666;">
                        Se você não solicitou esta recuperação, ignore este email. Sua senha não será alterada.
                    </p>
                </div>
                <div class="footer">
                    <p>Sistema Clinfec - ' . date('Y') . '</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        return $this->send($to, $subject, $body);
    }
}
