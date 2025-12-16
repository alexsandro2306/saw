<?php
/**
 * Sistema de Autenticação
 * Funções para login, logout e verificação de permissões
 */

// Prevenir acesso direto
if (!defined('SAW_APP')) {
      die('Acesso negado');
}

/**
 * Faz login de um utilizador
 * @param string $email Email
 * @param string $password Password
 * @param bool $remember_me Lembrar login
 * @return bool True se login bem-sucedido
 */
function login_user($email, $password, $remember_me = false)
{
      // Sanitizar email
      $email = sanitize_email($email);

      // Buscar utilizador
      $query = "SELECT * FROM utilizadores WHERE email = ?";
      $result = db_query($query, 's', [$email]);

      if (!$result || $result->num_rows === 0) {
            set_flash('error', 'Email ou password incorretos.');
            return false;
      }

      $user = $result->fetch_assoc();

      // Verificar password
      if (!password_verify($password, $user['password'])) {
            set_flash('error', 'Email ou password incorretos.');
            return false;
      }

      // Regenerar ID da sessão para prevenir fixação
      session_regenerate_id(true);

      // Guardar dados na sessão
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_name'] = $user['nome'];
      $_SESSION['user_email'] = $user['email'];
      $_SESSION['user_type'] = $user['tipo'];
      $_SESSION['logged_in'] = true;
      $_SESSION['last_activity'] = time();

      // Remember me
      if ($remember_me) {
            $token = generate_token(32);

            // Guardar token na BD
            $query = "UPDATE utilizadores SET remember_token = ? WHERE id = ?";
            db_execute($query, 'si', [$token, $user['id']]);

            // Criar cookie
            setcookie('remember_token', $token, time() + REMEMBER_ME_LIFETIME, '/', '', false, true);
            setcookie('remember_user', $user['id'], time() + REMEMBER_ME_LIFETIME, '/', '', false, true);
      }

      return true;
}

/**
 * Faz logout do utilizador
 */
function logout_user()
{
      // Remover remember token da BD
      if (isset($_SESSION['user_id'])) {
            $query = "UPDATE utilizadores SET remember_token = NULL WHERE id = ?";
            db_execute($query, 'i', [$_SESSION['user_id']]);
      }

      // Remover cookies
      if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            setcookie('remember_user', '', time() - 3600, '/');
      }

      // Destruir sessão
      session_destroy_secure();
}

/**
 * Verifica se o utilizador está autenticado
 * @return bool
 */
function is_logged_in()
{
      return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Verifica se o utilizador é administrador
 * @return bool
 */
function is_admin()
{
      return is_logged_in() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Verifica se o utilizador é user
 * @return bool
 */
function is_user()
{
      return is_logged_in() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'user';
}

/**
 * Obtém o ID do utilizador atual
 * @return int|null
 */
function get_current_user_id()
{
      return $_SESSION['user_id'] ?? null;
}

/**
 * Obtém dados do utilizador atual
 * @return array|null
 */
/**
 * Obtém dados do utilizador atual (função interna do SAW)
 * @return array|null
 */
function get_current_user_data()
{
      if (!is_logged_in()) {
            return null;
      }

      $user_id = get_current_user_id();
      $query = "SELECT * FROM utilizadores WHERE id = ?";
      $result = db_query($query, 'i', [$user_id]);

      if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
      }

      return null;
}

/**
 * Requer autenticação - redireciona para login se não autenticado
 */
function require_login()
{
      if (!is_logged_in()) {
            set_flash('warning', 'Por favor, faça login para aceder a esta página.');
            redirect(BASE_URL . '/login.php');
      }
}

/**
 * Requer permissões de administrador
 */
function require_admin()
{
      require_login();

      if (!is_admin()) {
            set_flash('error', 'Não tem permissões para aceder a esta página.');
            redirect(BASE_URL . '/index.php');
      }
}

/**
 * Requer permissões de user
 */
function require_user()
{
      require_login();

      if (!is_user()) {
            set_flash('error', 'Não tem permissões para aceder a esta página.');
            redirect(BASE_URL . '/index.php');
      }
}

/**
 * Tenta fazer login automático via remember me cookie
 * @return bool
 */
function auto_login()
{
      // Verificar se já está autenticado
      if (is_logged_in()) {
            return true;
      }

      // Verificar se existem cookies
      if (!isset($_COOKIE['remember_token']) || !isset($_COOKIE['remember_user'])) {
            return false;
      }

      $token = $_COOKIE['remember_token'];
      $user_id = (int) $_COOKIE['remember_user'];

      // Buscar utilizador
      $query = "SELECT * FROM utilizadores WHERE id = ? AND remember_token = ?";
      $result = db_query($query, 'is', [$user_id, $token]);

      if (!$result || $result->num_rows === 0) {
            // Token inválido, remover cookies
            setcookie('remember_token', '', time() - 3600, '/');
            setcookie('remember_user', '', time() - 3600, '/');
            return false;
      }

      $user = $result->fetch_assoc();

      // Regenerar ID da sessão
      session_regenerate_id(true);

      // Guardar dados na sessão
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_name'] = $user['nome'];
      $_SESSION['user_email'] = $user['email'];
      $_SESSION['user_type'] = $user['tipo'];
      $_SESSION['logged_in'] = true;
      $_SESSION['last_activity'] = time();

      return true;
}

/**
 * Regista um novo utilizador
 * @param string $nome Nome
 * @param string $email Email
 * @param string $password Password
 * @return int|false ID do utilizador criado ou false
 */
function register_user($nome, $email, $password)
{
      // Sanitizar dados
      $nome = sanitize_string($nome);
      $email = sanitize_email($email);

      // Validar
      $errors = [];

      if (!validate_name($nome)) {
            $errors[] = "Nome inválido.";
      }

      if (!validate_email($email)) {
            $errors[] = "Email inválido.";
      }

      if (email_exists($email)) {
            $errors[] = "Este email já está registado.";
      }

      if (!validate_password($password, $errors)) {
            // Erros já adicionados pela função
      }

      if (!empty($errors)) {
            foreach ($errors as $error) {
                  set_flash('error', $error);
            }
            return false;
      }

      // Hash da password
      $password_hash = password_hash($password, PASSWORD_DEFAULT);

      // Inserir na BD
      $query = "INSERT INTO utilizadores (nome, email, password, tipo) VALUES (?, ?, ?, 'user')";
      $user_id = db_execute($query, 'sss', [$nome, $email, $password_hash]);

      if ($user_id) {
            set_flash('success', 'Registo efetuado com sucesso! Por favor, faça login.');
            return $user_id;
      }

      set_flash('error', 'Erro ao criar conta. Por favor, tente novamente.');
      return false;
}

// Tentar auto-login
auto_login();
