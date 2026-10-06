<?php
/**
 * =====================================================================
 *  ARCHIVO  : actualizar.php
 *  PROYECTO : MVP Ventas de Ropa
 *  OPERACIÓN: UPDATE (actualizar) — parte 2 de 2
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTE ARCHIVO?
 *  UNA sola cosa: recibir el formulario editado de editar.php y aplicar
 *  el cambio en la base de datos.
 *
 *  No muestra nada. Es el gemelo de guardar.php, con dos diferencias:
 *     1. Usa UPDATE en vez de INSERT
 *     2. Recibe además el id, para saber CUÁL fila cambiar
 *
 *  EL RECORRIDO COMPLETO DE UNA EDICIÓN:
 *     consultar.php → editar.php → actualizar.php → consultar.php
 *     (clic Editar)   (formulario   (aplica el       (lista con
 *                      precargado)   cambio)          el aviso)
 *
 *  ¿QUÉ RECIBE? Los datos editados y el id, por POST.
 *  ¿QUÉ ENTREGA? Nada visible. Redirige.
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */

require_once 'includes/sesion.php';
exigir_sesion();
require_once 'includes/funciones.php';
require_once 'config/conexion.php';


/* =====================================================================
 * PASO 0 — VERIFICAR QUE LLEGÓ POR POST
 * ===================================================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: consultar.php');
    exit;
}


/* =====================================================================
 * PASO 1 — RECIBIR Y VALIDAR EL ID
 * =====================================================================
 * El id viene de un campo oculto del formulario de editar.php.
 *
 * (int) convierte a número entero. Si llegara "abc" se vuelve 0; si
 * llegara "5; DROP TABLE prendas" se vuelve 5. Esta conversión es,
 * por sí sola, una defensa contra inyección para valores numéricos.
 *
 * ¡OJO! Que el campo sea "oculto" NO significa que sea seguro: el
 * usuario puede cambiarlo con las herramientas del navegador. Por eso
 * el servidor SIEMPRE vuelve a revisar lo que le llega.
 * ===================================================================== */
$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: consultar.php?estado=no_existe');
    exit;
}


/* =====================================================================
 * PASO 2 — RECOGER LOS DATOS EDITADOS
 * ===================================================================== */
$datos = [
    'nombre'     => trim($_POST['nombre']     ?? ''),
    'talla'      => trim($_POST['talla']      ?? ''),
    'color'      => trim($_POST['color']      ?? ''),
    'precio'     => trim($_POST['precio']     ?? ''),
    'stock'      => trim($_POST['stock']      ?? ''),
    'imagen_url' => trim($_POST['imagen_url'] ?? ''),
];


/* =====================================================================
 * PASO 3 — VALIDAR
 * =====================================================================
 * Se usa EXACTAMENTE la misma función que en guardar.php.
 *
 * Esa es la ventaja de tener las funciones en un archivo aparte: las
 * reglas del negocio viven en UN solo lugar. Si mañana se decide que
 * el precio máximo es otro, se cambia en funciones.php y cambia tanto
 * al registrar como al editar. Si cada archivo tuviera su propia copia
 * de las reglas, tarde o temprano se desincronizan y el sistema acepta
 * al editar lo que rechaza al registrar.
 * ===================================================================== */
$errores = validar_prenda($datos);

if (!empty($errores)) {
    // Devolvemos al formulario de edición, conservando lo escrito.
    // Fíjate que el id viaja en la dirección, para que editar.php
    // sepa cuál prenda volver a mostrar.
    $_SESSION['errores_formulario'] = $errores;
    $_SESSION['datos_formulario']   = $datos;

    header('Location: editar.php?id=' . $id);
    exit;
}


/* =====================================================================
 * PASO 4 — PREPARAR LOS DATOS
 * ===================================================================== */
$nombre = $datos['nombre'];
$talla  = $datos['talla'];
$color  = $datos['color']      !== '' ? $datos['color']      : null;
$imagen = $datos['imagen_url'] !== '' ? $datos['imagen_url'] : null;
$precio = (float) $datos['precio'];
$stock  = (int)   $datos['stock'];


/* =====================================================================
 * PASO 5 — ACTUALIZAR CON SENTENCIA PREPARADA
 * =====================================================================
 * UPDATE tabla SET columna = valor, ... WHERE condición
 *
 * ############################################################
 * #  EL  WHERE id = ?  NO ES OPCIONAL.                       #
 * #                                                          #
 * #  Sin él, este UPDATE le cambiaría el nombre, la talla,   #
 * #  el precio y el stock a TODAS las prendas de la tabla,   #
 * #  dejándolas todas iguales. Y no hay deshacer.            #
 * #                                                          #
 * #  Antes de ejecutar cualquier UPDATE o DELETE: revisa     #
 * #  dos veces que tenga su WHERE.                           #
 * ############################################################
 *
 * Aquí hay 7 signos ?: seis de las columnas que cambian, y uno más al
 * final para el WHERE.
 *
 * Como el WHERE va de ÚLTIMO en la consulta, su letra va de ÚLTIMA en
 * bind_param. Por eso "sssdis" (los 6 campos) + "i" (el id) = "sssdisi"
 * ===================================================================== */

$stmt = $conexion->prepare(
    'UPDATE prendas
     SET nombre = ?, talla = ?, color = ?, precio = ?, stock = ?, imagen_url = ?
     WHERE id = ?'
);

$stmt->bind_param(
    'sssdisi',
    $nombre,     // s
    $talla,      // s
    $color,      // s
    $precio,     // d
    $stock,      // i
    $imagen,     // s
    $id          // i  ← este es el del WHERE
);

$stmt->execute();
$stmt->close();


/* =====================================================================
 * PASO 6 — REDIRIGIR (patrón PRG, igual que en guardar.php)
 * ===================================================================== */
header('Location: consultar.php?estado=editado');
exit;
