<?php
/**
 * Validação de Dados
 * Funções para validar inputs do utilizador
 */

// Prevenir acesso direto
if (!defined('SAW_APP')) {
      die('Acesso negado');
}

/**
 * Valida um endereço de email
 * @param string $email Email a validar
 * @return bool
 */
function validate_email($email)
{
      return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida uma password
 * @param string $password Password a validar
 * @param array &$errors Array para armazenar erros
 * @return bool
 */
function validate_password($password, &$errors = [])
{
      $valid = true;

      // Comprimento mínimo
      if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = "A password deve ter pelo menos " . PASSWORD_MIN_LENGTH . " caracteres.";
            $valid = false;
      }

      // Pelo menos uma letra maiúscula
      if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "A password deve conter pelo menos uma letra maiúscula.";
            $valid = false;
      }

      // Pelo menos uma letra minúscula
      if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "A password deve conter pelo menos uma letra minúscula.";
            $valid = false;
      }

      // Pelo menos um número
      if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "A password deve conter pelo menos um número.";
            $valid = false;
      }

      // Pelo menos um caractere especial
      if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = "A password deve conter pelo menos um caractere especial.";
            $valid = false;
      }

      return $valid;
}

/**
 * Valida um nome
 * @param string $name Nome a validar
 * @return bool
 */
function validate_name($name)
{
      // Mínimo 2 caracteres, apenas letras, espaços e alguns caracteres especiais
      return strlen($name) >= 2 && preg_match('/^[a-zA-ZÀ-ÿ\s\'-]+$/', $name);
}

/**
 * Valida uma data
 * @param string $date Data em formato Y-m-d
 * @return bool
 */
function validate_date($date)
{
      $d = DateTime::createFromFormat('Y-m-d', $date);
      return $d && $d->format('Y-m-d') === $date;
}

/**
 * Valida uma hora
 * @param string $time Hora em formato H:i
 * @return bool
 */
function validate_time($time)
{
      return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time);
}

/**
 * Valida se a data é futura
 * @param string $date Data em formato Y-m-d
 * @return bool
 */
function validate_future_date($date)
{
      if (!validate_date($date)) {
            return false;
      }

      $input_date = new DateTime($date);
      $today = new DateTime('today');

      return $input_date >= $today;
}

/**
 * Valida um ano
 * @param int $year Ano
 * @param int $min_year Ano mínimo
 * @param int $max_year Ano máximo (null = ano atual)
 * @return bool
 */
function validate_year($year, $min_year = 1900, $max_year = null)
{
      if ($max_year === null) {
            $max_year = (int) date('Y');
      }

      return is_numeric($year) && $year >= $min_year && $year <= $max_year;
}

/**
 * Valida um número inteiro positivo
 * @param mixed $number Número a validar
 * @return bool
 */
function validate_positive_int($number)
{
      return filter_var($number, FILTER_VALIDATE_INT) !== false && $number > 0;
}

/**
 * Valida um número decimal positivo
 * @param mixed $number Número a validar
 * @return bool
 */
function validate_positive_float($number)
{
      return filter_var($number, FILTER_VALIDATE_FLOAT) !== false && $number > 0;
}

/**
 * Valida um valor enum
 * @param mixed $value Valor a validar
 * @param array $allowed_values Valores permitidos
 * @return bool
 */
function validate_enum($value, $allowed_values)
{
      return in_array($value, $allowed_values, true);
}

/**
 * Valida campos obrigatórios
 * @param array $fields Array associativo com nome_campo => valor
 * @param array &$errors Array para armazenar erros
 * @return bool
 */
function validate_required_fields($fields, &$errors = [])
{
      $valid = true;

      foreach ($fields as $field_name => $field_value) {
            if (empty(trim($field_value))) {
                  $errors[] = "O campo '{$field_name}' é obrigatório.";
                  $valid = false;
            }
      }

      return $valid;
}

/**
 * Valida se um email já existe na base de dados
 * @param string $email Email a verificar
 * @param int $exclude_id ID do utilizador a excluir da verificação (para edição)
 * @return bool True se o email já existe
 */
function email_exists($email, $exclude_id = null)
{
      $query = "SELECT id FROM utilizadores WHERE email = ?";
      $params = [$email];
      $types = 's';

      if ($exclude_id !== null) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
            $types .= 'i';
      }

      $result = db_query($query, $types, $params);

      return $result && $result->num_rows > 0;
}

/**
 * Valida disponibilidade de horário para test drive
 * @param string $date Data
 * @param string $time Hora
 * @param int $exclude_id ID da reserva a excluir (para edição)
 * @return bool True se o horário está disponível
 */
function is_time_slot_available($date, $time, $exclude_id = null)
{
      $query = "SELECT id FROM reservas WHERE data_reserva = ? AND hora_reserva = ? AND estado != 'cancelada'";
      $params = [$date, $time];
      $types = 'ss';

      if ($exclude_id !== null) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
            $types .= 'i';
      }

      $result = db_query($query, $types, $params);

      return $result && $result->num_rows === 0;
}
