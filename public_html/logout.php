<?php
/**
 * Logout
 */

define('SAW_APP', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/funcoes.php';
require_once '../includes/auth.php';

logout_user();
set_flash('success', 'Logout efetuado com sucesso.');
redirect(BASE_URL . '/index.php');
