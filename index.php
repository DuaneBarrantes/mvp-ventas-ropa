<?php
/**
 * =====================================================================
 *  ARCHIVO  : index.php
 *  PROYECTO : MVP Ventas de Ropa
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTE ARCHIVO?
 *  Es la PUERTA DE ENTRADA del proyecto. No muestra nada: solo decide
 *  a dónde mandar a quien llegue.
 *
 *     ¿Ya inició sesión?  → lo manda a consultar.php (el inventario)
 *     ¿Todavía no?        → lo manda a login.php
 *
 *  ¿POR QUÉ SE LLAMA index.php Y NO OTRA COSA?
 *  Porque es el nombre que el servidor abre AUTOMÁTICAMENTE cuando
 *  alguien entra a una carpeta sin escribir un archivo. Al teclear:
 *
 *      http://localhost/mvp-ventas-ropa/
 *
 *  Apache busca index.php por su cuenta. Si este archivo no existiera,
 *  el navegador mostraría la lista de archivos de la carpeta — que es
 *  feo y además revela la estructura interna del proyecto a cualquiera.
 *
 *  ¿POR QUÉ NO PONER AQUÍ DIRECTAMENTE EL INVENTARIO?
 *  Se podría, pero entonces este archivo tendría dos trabajos: ser la
 *  entrada y ser el listado. Prefiere hacer una sola cosa. Además, así
 *  el listado tiene un nombre que dice lo que hace: consultar.php.
 *
 *  ¿QUÉ RECIBE?  Nada.
 *  ¿QUÉ ENTREGA? Nada visible. Solo redirige.
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */

// Cargamos el archivo de sesión, que trae la función hay_sesion_activa()
require_once 'includes/sesion.php';


// Si hay alguien con sesión iniciada, va derecho al inventario.
if (hay_sesion_activa()) {
    header('Location: consultar.php');
    exit;
}

// Si no, al login.
header('Location: login.php');

// exit; detiene el programa aquí mismo.
//
// Es OBLIGATORIO después de un header() de redirección: header() solo
// ENVÍA la orden al navegador, pero PHP seguiría ejecutando lo que
// venga abajo. Sin el exit, se alcanzaría a imprimir contenido que el
// usuario no debería ver.
exit;
