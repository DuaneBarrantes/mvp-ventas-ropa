<?php
/**
 * =====================================================================
 *  ARCHIVO  : guardar.php
 *  PROYECTO : MVP Ventas de Ropa
 *  OPERACIÓN: CREATE (crear) — parte 2 de 2
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTE ARCHIVO?
 *  UNA sola cosa: recibir los datos del formulario de registrar.php,
 *  revisarlos y guardarlos en la base de datos.
 *
 *  Este archivo NO muestra nada en pantalla. El usuario nunca ve esta
 *  página: entra, hace su trabajo y redirige. Por eso no tiene una
 *  sola etiqueta HTML.
 *
 *  EL RECORRIDO COMPLETO DE UN REGISTRO:
 *     registrar.php  →  guardar.php  →  consultar.php
 *     (formulario)      (inserta)        (lista)
 *
 *  ¿QUÉ RECIBE? Los datos del formulario, por POST.
 *  ¿QUÉ ENTREGA? Nada visible. Redirige a consultar.php si guardó, o
 *                de vuelta a registrar.php si hubo errores.
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */

require_once 'includes/sesion.php';
exigir_sesion();                      // También protegido: nadie inserta sin sesión
require_once 'includes/funciones.php';
require_once 'config/conexion.php';


/* =====================================================================
 * PASO 0 — VERIFICAR QUE LLEGÓ POR POST
 * =====================================================================
 * Si alguien escribe guardar.php directamente en el navegador, llega
 * por GET y $_POST viene vacío. Sin esta revisión, el programa
 * intentaría guardar una prenda en blanco.
 *
 * Es una barrera de sentido común: este archivo SOLO tiene sentido
 * cuando lo llama un formulario.
 * ===================================================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registrar.php');
    exit;
}


/* =====================================================================
 * PASO 1 — RECOGER LO QUE ESCRIBIÓ EL USUARIO
 * =====================================================================
 * $_POST es el arreglo con los datos del formulario. La casilla se
 * llama igual que el atributo name="" de cada input en registrar.php.
 * Si los nombres no coinciden, el dato simplemente no llega.
 *
 * trim() quita los espacios en blanco del principio y del final. Sin
 * él, alguien podría mandar solo espacios "   " y pasaría como si
 * fuera un nombre válido.
 *
 * ?? '' evita el error "Undefined index" si un campo no llegó.
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
 * PASO 2 — VALIDAR
 * =====================================================================
 * Llamamos a la función que vive en includes/funciones.php. Devuelve
 * una lista de errores; si está vacía, todo pasó.
 *
 * ¡ESTA ES LA VALIDACIÓN QUE DE VERDAD PROTEGE!
 *
 * Ya validamos en el navegador con validaciones.js, ¿por qué otra vez?
 * Porque JavaScript corre en el computador del USUARIO, y el usuario
 * lo puede desactivar, modificar o saltárselo completamente. Alguien
 * puede mandar datos a este archivo sin pasar por nuestro formulario.
 *
 * PHP corre en el SERVIDOR, donde el usuario no puede tocarlo.
 *
 * Para memorizar: "la validación del cliente es por comodidad; la del
 * servidor es por seguridad".
 * ===================================================================== */
$errores = validar_prenda($datos);


/* =====================================================================
 * PASO 3 — SI HAY ERRORES, DEVOLVER AL FORMULARIO
 * ===================================================================== */
if (!empty($errores)) {

    // Dejamos los errores y lo que el usuario escribió en la sesión,
    // para que registrar.php los recoja y no le toque escribir todo
    // otra vez. (En registrar.php está explicado con detalle.)
    $_SESSION['errores_formulario'] = $errores;
    $_SESSION['datos_formulario']   = $datos;

    header('Location: registrar.php');
    exit;
}


/* =====================================================================
 * PASO 4 — PREPARAR LOS DATOS PARA LA BASE DE DATOS
 * ===================================================================== */

$nombre = $datos['nombre'];
$talla  = $datos['talla'];

// Para color e imagen: si el usuario los dejó vacíos, guardamos NULL
// (que significa "sin dato") y no una cadena vacía "".
//
// ¿Cuál es la diferencia? NULL dice "esta prenda no tiene color".
// La cadena vacía diría "su color es la nada". El primero es correcto.
$color  = $datos['color']      !== '' ? $datos['color']      : null;
$imagen = $datos['imagen_url'] !== '' ? $datos['imagen_url'] : null;

// Convertimos texto a número. Todo lo que llega de un formulario llega
// como TEXTO, aunque el usuario haya escrito 45000.
//   (float) para el precio → admite decimales
//   (int)   para el stock  → solo enteros
$precio = (float) $datos['precio'];
$stock  = (int)   $datos['stock'];


/* =====================================================================
 * PASO 5 — GUARDAR CON SENTENCIA PREPARADA
 * =====================================================================
 * ¡NUNCA se concatenan los datos dentro del texto del SQL!
 *
 * Si escribiéramos:
 *     "INSERT INTO prendas (nombre) VALUES ('$nombre')"
 * alguien podría escribir en el campo nombre algo como:
 *     '); DROP TABLE prendas; --
 * y borraría la tabla entera. Eso se llama INYECCIÓN SQL.
 *
 * Con la sentencia preparada le mandamos a MySQL primero la ORDEN (con
 * signos ? en los huecos) y DESPUÉS los datos por separado. Como MySQL
 * ya sabe cuál es la orden antes de ver los datos, trata lo que llega
 * como texto y nunca como instrucción. Así el ataque es imposible.
 * ===================================================================== */

// 5.1 — Preparar: la orden con 6 huecos, uno por columna.
//       (el id no va porque es AUTO_INCREMENT, lo pone MySQL solo;
//        creado_en tampoco, porque tiene DEFAULT CURRENT_TIMESTAMP)
$stmt = $conexion->prepare(
    'INSERT INTO prendas (nombre, talla, color, precio, stock, imagen_url)
     VALUES (?, ?, ?, ?, ?, ?)'
);

// 5.2 — Amarrar cada valor a su hueco.
//
// Las letras indican el TIPO de cada dato, EN ORDEN:
//     s = string  (texto)
//     i = integer (número entero)
//     d = double  (número con decimales)
//     b = blob    (archivo binario)
//
//   nombre     → s
//   talla      → s
//   color      → s
//   precio     → d
//   stock      → i
//   imagen_url → s
//                 = "sssdis"
//
// LA CUENTA QUE SIEMPRE HAY QUE HACER:
//   signos ?  →  6
//   letras    →  6  (s-s-s-d-i-s)
//   variables →  6
// Si un solo número no cuadra, truena. Es lo primero que hay que
// revisar cuando algo no funciona.
$stmt->bind_param(
    'sssdis',
    $nombre,
    $talla,
    $color,
    $precio,
    $stock,
    $imagen
);

// 5.3 — Ejecutar: aquí es cuando el dato realmente se graba
$stmt->execute();

// 5.4 — Cerrar y liberar la memoria que usaba la consulta
$stmt->close();


/* =====================================================================
 * PASO 6 — REDIRIGIR
 * =====================================================================
 * Después de guardar NUNCA se muestra el resultado directamente: se
 * redirige a otra página.
 *
 * ¿POR QUÉ? Porque si nos quedáramos aquí y el usuario apretara F5, el
 * navegador volvería a enviar el formulario y la prenda se guardaría
 * DOS veces. Ese problema se llama "doble envío".
 *
 * Al redirigir, apretar F5 solo repite la página de la lista, que es
 * inofensiva.
 *
 * A este patrón se le dice PRG (Post - Redirect - Get):
 *    1. POST     → se guarda el dato          (este archivo)
 *    2. REDIRECT → se manda a otra dirección  (esta línea)
 *    3. GET      → esa página solo muestra    (consultar.php)
 *
 * Y el exit; de abajo es OBLIGATORIO: header() solo ENVÍA la orden,
 * pero PHP seguiría ejecutando lo que venga después.
 * ===================================================================== */
header('Location: consultar.php?estado=creado');
exit;
