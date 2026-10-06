<?php
/**
 * =====================================================================
 *  ARCHIVO  : config/conexion.ejemplo.php
 *  PROYECTO : MVP Ventas de Ropa
 * ---------------------------------------------------------------------
 *  ¿PARA QUÉ SIRVE ESTE ARCHIVO?
 *
 *  Este es el archivo PLANTILLA de la conexión. Es el que SÍ se sube a
 *  GitHub, porque no contiene ninguna contraseña real.
 *
 *  El archivo verdadero (config/conexion.php) NO se sube nunca, porque
 *  ahí van los datos reales de acceso a la base de datos. Está bloqueado
 *  en el archivo .gitignore.
 *
 *  ¿POR QUÉ SE HACE ASÍ?
 *  Porque GitHub es público. Si subiéramos las contraseñas, cualquiera
 *  podría entrar a la base de datos. Esta es una práctica profesional
 *  estándar: el código se comparte, las contraseñas no.
 *
 *  ¿QUÉ DEBE HACER QUIEN DESCARGUE EL PROYECTO?
 *    1. Copiar este archivo
 *    2. Renombrar la copia a  conexion.php
 *    3. Escribir sus propios datos de acceso
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */

// Dirección del servidor de base de datos.
// Si MySQL está en el mismo computador, se deja 127.0.0.1
define('DB_SERVIDOR', '127.0.0.1');

// Usuario de MySQL. En XAMPP por defecto es 'root'.
define('DB_USUARIO', 'ESCRIBE_AQUI_TU_USUARIO');

// Contraseña de ese usuario. En XAMPP por defecto va vacía: ''
define('DB_CLAVE', 'ESCRIBE_AQUI_TU_CLAVE');

// Nombre de la base de datos (la que crea base_datos.sql)
define('DB_NOMBRE', 'mvp_ropa');

// Puerto donde escucha MySQL. Normalmente es el 3306.
// Confírmalo: panel de XAMPP → Config de MySQL → my.ini → [mysqld] → port=
define('DB_PUERTO', 3306);


// A partir de aquí el contenido es idéntico al de conexion.php.
// Ver ese archivo para los comentarios completos de cada línea.

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = new mysqli(DB_SERVIDOR, DB_USUARIO, DB_CLAVE, DB_NOMBRE, DB_PUERTO);
    $conexion->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('No se pudo conectar a la base de datos: ' . htmlspecialchars($e->getMessage()));
}
