<?php
/**
 * =====================================================================
 *  ARCHIVO  : logout.php
 *  PROYECTO : MVP Ventas de Ropa
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTA PÁGINA?
 *  Cierra la sesión del usuario y lo devuelve al login.
 *
 *  Es la página más corta del proyecto, y no muestra NADA en pantalla:
 *  solo hace su trabajo y redirige. El usuario ni alcanza a verla.
 *
 *  ¿POR QUÉ NO TIENE HTML?
 *  Porque header('Location: ...') debe enviarse ANTES de imprimir
 *  cualquier cosa. Si hubiera aunque fuera un espacio en blanco antes,
 *  saldría el error "headers already sent".
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */

// Cargamos el archivo de sesión, que trae la función cerrar_sesion()
require_once 'includes/sesion.php';

// Borramos todo rastro de la sesión (los tres pasos están explicados
// dentro de includes/sesion.php)
cerrar_sesion();

// Devolvemos al usuario al login con un aviso de despedida.
// El ?estado=sesion_fin hace que aparezca el mensaje verde
// "Cerraste sesión correctamente".
header('Location: login.php?estado=sesion_fin');

// exit detiene el programa aquí. Es obligatorio después de un header
// de redirección: sin él, PHP seguiría ejecutando lo que venga abajo.
exit;
