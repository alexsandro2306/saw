<?php
/**
 * Ficheiro de Configuração
 * Configurações globais da aplicação
 */

// Prevenir acesso direto
if (!defined('SAW_APP')) {
    die('Acesso negado');
}

// Configurações da Base de Dados
define('DB_HOST', 'saw');
define('DB_USER', 'root');
define('DB_PASS', 'Porto.2003');
define('DB_NAME', 'saw');
define('DB_CHARSET', 'utf8mb4');

// Configurações de Paths
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('PUBLIC_PATH', BASE_PATH . '/public_html');
define('IMAGES_PATH', PUBLIC_PATH . '/images');
define('LOGS_PATH', BASE_PATH . '/logs');

// Configurações de URL (ajustar conforme o ambiente)
define('BASE_URL', 'https://saw.pt');
define('IMAGES_URL', BASE_URL . '/images');

// Configurações de Sessão
define('SESSION_NAME', 'SAW_SESSION');
define('SESSION_LIFETIME', 7200); // 2 horas em segundos
define('REMEMBER_ME_LIFETIME', 2592000); // 30 dias em segundos

// Configurações de Email (PHPMailer)
define('SMTP_HOST', 'smtp.gmail.com');        // Servidor SMTP
define('SMTP_PORT', 587);                      // Porta (587 para TLS)
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'alexsandrooliveira23102006@gmail.com'); // Email
define('SMTP_PASSWORD', 'lwgu cxsl hwtr kkbs');   // Password de aplicação
define('SMTP_FROM_EMAIL', 'noreply@saw.pt');   // Email remetente
define('SMTP_FROM_NAME', 'Stand Automóvel SAW');

// Configurações de Upload
define('MAX_FILE_SIZE', 5242880); // 5MB em bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Configurações de Segurança
define('PASSWORD_MIN_LENGTH', 8);
define('RESET_TOKEN_EXPIRY', 3600); // 1 hora em segundos

// Timezone
date_default_timezone_set('Europe/Lisbon');

// Configurações de Erro (desenvolvimento)
// Em produção, alterar para false
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/saw-error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/saw-error.log');
}

// Configurações de Horário para Test Drives
define('TEST_DRIVE_HORA_INICIO', '09:00');
define('TEST_DRIVE_HORA_FIM', '18:00');
define('TEST_DRIVE_INTERVALO', 60); // minutos
