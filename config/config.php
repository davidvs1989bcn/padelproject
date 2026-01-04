<?php
// DETECCIÓN AUTOMÁTICA DE BASE_URL
$scriptName = $_SERVER['SCRIPT_NAME'];
$dirName = dirname($scriptName);

// Normalizar barra invertida en Windows
$backslash = chr(92);
$dirName = str_replace($backslash, '/', $dirName);

// Asegurar que no hay barra al final (excepto si es raíz)
if ($dirName !== '/') {
    $dirName = rtrim($dirName, '/');
} else {
    $dirName = '';
}

define('BASE_URL', $dirName);
define('ASSETS_URL', BASE_URL . '/public');
define('APP_NAME', 'PadelPro');
