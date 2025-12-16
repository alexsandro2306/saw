<?php
/**
 * Envio de Emails com PHPMailer
 */

if (!defined('SAW_APP')) {
    die('Acesso negado');
}

// Importar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once BASE_PATH . '/vendor/autoload.php';

/**
 * Configura e retorna instância do PHPMailer
 */
function get_mailer() {
    $mail = new PHPMailer(true);
    
    try {
        // Configurações do servidor
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Remetente padrão
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        return $mail;
    } catch (Exception $e) {
        error_log("PHPMailer Config Error: {$e->getMessage()}");
        return false;
    }
}

/**
 * Envia um email
 */
function send_email($to, $subject, $message) {
    $mail = get_mailer();
    
    if (!$mail) {
        return false;
    }
    
    try {
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Envia email de recuperação de password
 */
function send_password_reset_email($email) {
    // Verificar se o email existe
    $query = "SELECT id, nome FROM utilizadores WHERE email = ?";
    $result = db_query($query, 's', [$email]);
    
    if (!$result || $result->num_rows === 0) {
        return true; // Não revelar se existe
    }
    
    $user = $result->fetch_assoc();
    
    // Gerar token
    $token = generate_token(32);
    $expiry = date('Y-m-d H:i:s', time() + RESET_TOKEN_EXPIRY);
    
    // Guardar token
    $query = "INSERT INTO reset_tokens (utilizador_id, token, expira_em) VALUES (?, ?, ?)";
    $result = db_execute($query, 'iss', [$user['id'], $token, $expiry]);
    
    if (!$result) {
        error_log("Failed to create reset token for user: {$user['id']}");
        return false;
    }
    
    // Criar link
    $reset_link = BASE_URL . '/reset_password.php?token=' . $token;
    
    // HTML do email
    $subject = 'Recuperação de Password - SAW';
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 30px; }
            .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Recuperação de Password</h1>
            </div>
            <div class="content">
                <p>Olá <strong>' . htmlspecialchars($user['nome']) . '</strong>,</p>
                <p>Recebemos um pedido para recuperar a password da sua conta no Stand Automóvel SAW.</p>
                <p>Para definir uma nova password, clique no botão abaixo:</p>
                <p style="text-align: center;">
                    <a href="' . $reset_link . '" class="button">Redefinir Password</a>
                </p>
                <p>Ou copie e cole o seguinte link no seu navegador:</p>
                <p style="word-break: break-all; background: white; padding: 10px; border-left: 3px solid #667eea;">
                    ' . $reset_link . '
                </p>
                <p><strong>Este link é válido por 1 hora.</strong></p>
                <p>Se não solicitou esta recuperação, pode ignorar este email. A sua password permanecerá inalterada.</p>
            </div>
            <div class="footer">
                <p>© ' . date('Y') . ' Stand Automóvel SAW. Todos os direitos reservados.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return send_email($email, $subject, $message);
}

/**
 * Verifica se um token de reset é válido
 * @param string $token Token
 * @return array|false Dados do utilizador ou false
 */
function verify_reset_token($token)
{
    $query = "
        SELECT rt.*, u.id as user_id, u.nome, u.email 
        FROM reset_tokens rt
        JOIN utilizadores u ON rt.utilizador_id = u.id
        WHERE rt.token = ? 
        AND rt.usado = FALSE 
        AND rt.expira_em > NOW()
    ";

    $result = db_query($query, 's', [$token]);

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return false;
}

/**
 * Redefine a password usando um token
 * @param string $token Token
 * @param string $new_password Nova password
 * @return bool
 */
function reset_password_with_token($token, $new_password)
{
    // Verificar token
    $token_data = verify_reset_token($token);

    if (!$token_data) {
        set_flash('error', 'Token inválido ou expirado.');
        return false;
    }

    // Validar password
    $errors = [];
    if (!validate_password($new_password, $errors)) {
        foreach ($errors as $error) {
            set_flash('error', $error);
        }
        return false;
    }

    // Hash da password
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Atualizar password
    $query = "UPDATE utilizadores SET password = ? WHERE id = ?";
    $result = db_execute($query, 'si', [$password_hash, $token_data['user_id']]);

    if (!$result) {
        set_flash('error', 'Erro ao redefinir password.');
        return false;
    }

    // Marcar token como usado
    $query = "UPDATE reset_tokens SET usado = TRUE WHERE token = ?";
    db_execute($query, 's', [$token]);

    set_flash('success', 'Password redefinida com sucesso! Por favor, faça login.');
    return true;
}