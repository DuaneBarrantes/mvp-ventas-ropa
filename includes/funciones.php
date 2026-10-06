<?php
/**
 * =====================================================================
 *  ARCHIVO  : includes/funciones.php
 *  PROYECTO : MVP Ventas de Ropa
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTE ARCHIVO?
 *  Guarda las funciones que se usan en VARIAS páginas del sistema. En
 *  vez de copiar y pegar el mismo código en 8 archivos, se escribe una
 *  sola vez aquí y cada página lo llama.
 *
 *  Esta es la idea de "componentes reutilizables" que pide la guía: si
 *  hay que corregir algo, se corrige en un solo lugar.
 *
 *  ¿CÓMO SE USA?
 *      require_once 'includes/funciones.php';
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */

/* =====================================================================
 * PASO 0 — FIJAR LA ZONA HORARIA
 * =====================================================================
 * Esta línea es corta y evita un error que solo aparece de noche — o
 * sea, el peor tipo de error: el que no se ve cuando uno prueba.
 *
 * EL PROBLEMA:
 * PHP y MySQL pueden tener zonas horarias distintas. Si PHP viene
 * configurado en UTC (que es lo normal si nadie lo tocó) y MySQL usa la
 * hora del computador, entonces a partir de las 7 de la noche en
 * Colombia pasa esto:
 *
 *     PHP dice   : hoy es 20 de septiembre   (ya cambió de día en UTC)
 *     MySQL dice : hoy es 19 de septiembre   (hora de Colombia)
 *
 * Colombia está 5 horas atrás de UTC, así que desde las 7:00 p. m. los
 * dos no se ponen de acuerdo sobre qué día es.
 *
 * ¿QUÉ SE DAÑA?
 * Todo lo que compare fechas entre los dos mundos. La validación de
 * "no se puede registrar en el pasado" usa date('Y-m-d') de PHP, y los
 * reportes usan CURDATE() de MySQL. Si no coinciden, el sistema
 * rechaza registros válidos y los reportes muestran el día equivocado.
 *
 * Y lo peor: al mediodía todo funciona perfecto. El error solo aparece
 * en la jornada de la tarde-noche.
 *
 * LA SOLUCIÓN:
 * Decirle a PHP explícitamente en qué zona horaria estamos. Nunca se
 * debe depender de la configuración que traiga el servidor por defecto.
 * ===================================================================== */
date_default_timezone_set('America/Bogota');



/* =====================================================================
 * SECCIÓN A — SEGURIDAD AL MOSTRAR DATOS
 * ===================================================================== */

/**
 * FUNCIÓN: limpiar()
 * ---------------------------------------------------------------------
 *  RECIBE  : $texto — cualquier texto que venga de la base de datos o
 *                     del usuario
 *  DEVUELVE: ese mismo texto, pero seguro para mostrar en pantalla
 *
 *  ¿PARA QUÉ SIRVE? (pregunta típica de sustentación)
 *
 *  Imagina que alguien registra una prenda con este nombre:
 *      <script>alert('hackeado')</script>
 *
 *  Si lo mostramos tal cual, el navegador NO lo ve como texto: lo ve
 *  como una orden y la ejecuta. Eso se llama ataque XSS
 *  (Cross-Site Scripting).
 *
 *  htmlspecialchars() convierte los caracteres peligrosos en su versión
 *  "dibujada":
 *      <  se vuelve  &lt;
 *      >  se vuelve  &gt;
 *      "  se vuelve  &quot;
 *
 *  Así el navegador lo muestra como texto normal en la pantalla, y no
 *  lo ejecuta.
 *
 *  REGLA DE ORO: todo dato que venga de la base de datos o del usuario
 *  se imprime con limpiar(). Sin excepciones.
 */
function limpiar($texto): string
{
    // (string) obliga a que sea texto. Si llega un número o un NULL,
    // lo convierte, evitando errores en PHP 8.
    // ENT_QUOTES: convierte tanto comillas dobles " como simples '
    // 'UTF-8': para que las tildes y la ñ no se dañen
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}


/* =====================================================================
 * SECCIÓN B — VALIDACIÓN DE LO QUE ESCRIBE EL USUARIO
 * ===================================================================== */

/**
 * FUNCIÓN: validar_prenda()
 * ---------------------------------------------------------------------
 *  RECIBE  : $datos — el arreglo con lo que el usuario escribió en el
 *                     formulario (normalmente $_POST)
 *  DEVUELVE: un arreglo con los mensajes de error encontrados.
 *            Si el arreglo sale VACÍO, es que todo está correcto.
 *
 *  ¿POR QUÉ VALIDAR EN PHP SI YA VALIDAMOS EN JAVASCRIPT?
 *  (Esta es LA pregunta que más cae en la sustentación.)
 *
 *  Porque la validación de JavaScript se puede saltar. Cualquiera puede
 *  desactivar JavaScript en el navegador, o mandar los datos con una
 *  herramienta externa sin pasar por nuestro formulario.
 *
 *  JavaScript valida para AYUDAR al usuario (avisarle rápido, sin
 *  recargar la página).
 *  PHP valida para PROTEGER el sistema (es la barrera de verdad).
 *
 *  Se dice así: "la validación del cliente es por comodidad, la del
 *  servidor es por seguridad".
 */
function validar_prenda(array $datos): array
{
    // Arreglo vacío donde iremos guardando los errores que encontremos
    $errores = [];

    // ---- NOMBRE ----
    // trim() quita los espacios en blanco del principio y del final.
    // Sin trim, alguien podría mandar solo espacios "   " y pasaría
    // como si fuera un nombre válido.
    $nombre = trim($datos['nombre'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre de la prenda es obligatorio.';
    } elseif (mb_strlen($nombre) > 60) {
        // mb_strlen (y no strlen) porque cuenta bien las tildes y la ñ.
        // strlen contaría la "ñ" como 2 caracteres.
        // El límite es 60 porque la columna es VARCHAR(60): si mandamos
        // más, MySQL lo corta o da error.
        $errores[] = 'El nombre no puede pasar de 60 caracteres.';
    }

    // ---- TALLA ----
    $talla = trim($datos['talla'] ?? '');
    // Lista cerrada de tallas permitidas.
    $tallas_validas = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    if ($talla === '') {
        $errores[] = 'Debes seleccionar una talla.';
    } elseif (!in_array($talla, $tallas_validas, true)) {
        // in_array pregunta "¿este valor está en esa lista?"
        // El tercer parámetro true exige que además sea del mismo tipo.
        //
        // ¿Por qué revisar esto si la talla viene de una lista
        // desplegable? Porque el desplegable está en el navegador, y el
        // navegador se puede manipular. Nunca se confía en lo que llega.
        $errores[] = 'La talla seleccionada no es válida.';
    }

    // ---- PRECIO ----
    $precio = $datos['precio'] ?? '';

    if ($precio === '') {
        $errores[] = 'El precio es obligatorio.';
    } elseif (!is_numeric($precio)) {
        // is_numeric revisa que sea un número. Si escriben "abc" o
        // "45.000" (con punto de miles), no pasa.
        $errores[] = 'El precio debe ser un número, sin puntos de miles.';
    } elseif ((float) $precio < 0) {
        // (float) convierte el texto a número con decimales.
        $errores[] = 'El precio no puede ser negativo.';
    } elseif ((float) $precio > 99999999.99) {
        // Tope de la columna DECIMAL(10,2)
        $errores[] = 'El precio supera el máximo permitido.';
    }

    // ---- STOCK ----
    $stock = $datos['stock'] ?? '';

    if ($stock === '') {
        $errores[] = 'El stock es obligatorio.';
    } elseif (!ctype_digit((string) $stock)) {
        // ctype_digit revisa que sean SOLO dígitos del 0 al 9.
        // Rechaza "3.5" y "-2", que es justo lo que queremos: no existe
        // media prenda ni stock negativo.
        $errores[] = 'El stock debe ser un número entero, sin decimales.';
    }

    // ---- COLOR (opcional) ----
    $color = trim($datos['color'] ?? '');
    if ($color !== '' && mb_strlen($color) > 30) {
        // Solo se valida si escribieron algo, porque el color puede ir
        // vacío (la columna acepta NULL).
        $errores[] = 'El color no puede pasar de 30 caracteres.';
    }

    // ---- IMAGEN (opcional) ----
    $imagen = trim($datos['imagen_url'] ?? '');
    if ($imagen !== '') {
        if (mb_strlen($imagen) > 255) {
            $errores[] = 'La dirección de la imagen es demasiado larga.';
        } elseif (!filter_var($imagen, FILTER_VALIDATE_URL)) {
            // filter_var con FILTER_VALIDATE_URL revisa que tenga forma
            // de dirección web (que empiece por http://, etc.)
            $errores[] = 'La dirección de la imagen no es una URL válida.';
        }
    }

    // Devolvemos la lista. Vacía = todo bien.
    return $errores;
}


/* =====================================================================
 * SECCIÓN C — AVISOS EN PANTALLA
 * ===================================================================== */

/**
 * FUNCIÓN: mensaje_estado()
 * ---------------------------------------------------------------------
 *  RECIBE  : $estado — una palabra clave que llega por la dirección web,
 *                      por ejemplo:  consultar.php?estado=creado
 *  DEVUELVE: el HTML del aviso de colores, o texto vacío si no aplica.
 *
 *  ¿POR QUÉ SE MANDA POR LA DIRECCIÓN (?estado=...) Y NO SE IMPRIME
 *  DIRECTO?
 *
 *  Porque después de guardar en la base de datos hacemos una
 *  REDIRECCIÓN. Si no redirigiéramos y el usuario apretara F5, el
 *  navegador volvería a mandar el formulario y se guardaría la prenda
 *  DOS VECES. Ese problema se llama "doble envío".
 *
 *  La solución se llama patrón PRG (Post - Redirect - Get):
 *     1. POST     → se guarda el dato
 *     2. REDIRECT → se manda al usuario a otra dirección
 *     3. GET      → esa página solo muestra, no guarda
 *  Así, apretar F5 solo repite el paso 3, que es inofensivo.
 */
function mensaje_estado(?string $estado): string
{
    // Si no llegó ningún estado, no hay nada que mostrar.
    if ($estado === null || $estado === '') {
        return '';
    }

    // Diccionario: a cada palabra clave le corresponde un color de
    // Bootstrap y un texto.
    //   success = verde   danger = rojo   warning = amarillo
    $avisos = [
        'creado'      => ['success', 'La prenda se registró correctamente.'],
        'editado'     => ['success', 'Los cambios se guardaron correctamente.'],
        'eliminado'   => ['success', 'La prenda se eliminó correctamente.'],
        'no_existe'   => ['danger',  'Esa prenda no existe o ya fue eliminada.'],
        'sin_sesion'  => ['warning', 'Debes iniciar sesión para entrar.'],
        'sesion_fin'  => ['success', 'Cerraste sesión correctamente.'],
        'error_login' => ['danger',  'Usuario o contraseña incorrectos.'],
    ];

    // Si la palabra que llegó no está en nuestro diccionario, no
    // mostramos nada. Esto evita que alguien invente un ?estado= con
    // texto malicioso y lo veamos impreso en la página.
    if (!isset($avisos[$estado])) {
        return '';
    }

    // list() reparte los dos valores del arreglo en dos variables
    [$color, $texto] = $avisos[$estado];

    // Devolvemos el HTML del aviso de Bootstrap
    return '<div class="alert alert-' . $color . ' alert-dismissible fade show" role="alert">'
         . limpiar($texto)
         . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>'
         . '</div>';
}


/* =====================================================================
 * SECCIÓN D — FORMATO DE DATOS PARA MOSTRAR
 * ===================================================================== */

/**
 * FUNCIÓN: formato_pesos()
 * ---------------------------------------------------------------------
 *  RECIBE  : $valor — un número, por ejemplo 45000
 *  DEVUELVE: ese número escrito como plata: "$ 45.000"
 *
 *  En la base de datos guardamos 45000.00 (número limpio, para poder
 *  hacer cuentas). Pero al usuario se le muestra con separadores,
 *  porque "45000" es difícil de leer de un vistazo.
 *
 *  REGLA IMPORTANTE: el formato es SOLO para mostrar. Jamás se guarda
 *  el precio con puntos en la base de datos, porque entonces no se
 *  podrían sumar ni ordenar.
 */
function formato_pesos($valor): string
{
    // number_format(número, decimales, separador_decimal, separador_miles)
    // En Colombia se usa la coma para decimales y el punto para miles.
    return '$ ' . number_format((float) $valor, 0, ',', '.');
}


/**
 * FUNCIÓN: etiqueta_stock()
 * ---------------------------------------------------------------------
 *  RECIBE  : $stock — cuántas unidades hay
 *  DEVUELVE: una etiqueta de color según la cantidad
 *
 *  Sirve para que de un vistazo se vea qué prenda está por agotarse.
 *  Es una regla de negocio: la decidimos nosotros, no viene de la
 *  base de datos.
 */
function etiqueta_stock(int $stock): string
{
    if ($stock === 0) {
        // Rojo: no queda nada
        return '<span class="badge text-bg-danger">Agotado</span>';
    }
    if ($stock <= 5) {
        // Amarillo: hay que pedir más pronto
        return '<span class="badge text-bg-warning">Quedan ' . $stock . '</span>';
    }
    // Verde: hay suficiente
    return '<span class="badge text-bg-success">' . $stock . ' disponibles</span>';
}
