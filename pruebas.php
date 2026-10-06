<?php
/**
 * =====================================================================
 *  ARCHIVO  : pruebas.php
 *  PROYECTO : MVP Ventas de Ropa
 *  GUÍA     : 2 — Casos de prueba con assert() casero
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTE ARCHIVO?
 *  Revisa automáticamente que las funciones del sistema devuelvan lo
 *  que deben devolver.
 *
 *  ¿PARA QUÉ SIRVE PROBAR ASÍ?
 *  Hasta ahora probábamos "a mano": abrir la página, llenar el
 *  formulario, mirar si salió bien. Eso funciona, pero tiene dos
 *  problemas:
 *    1. Es lento y aburrido, entonces uno termina probando poco.
 *    2. Cuando se cambia algo, hay que volver a probar TODO, y uno
 *       prueba solo lo que cambió. Ahí es donde se cuelan los errores.
 *
 *  Con pruebas automáticas se corre un archivo y en un segundo se
 *  sabe si algo se dañó.
 *
 *  ¿QUÉ ES assert()?
 *  Es "afirmar" algo que debe ser cierto. Se le dice al programa:
 *  "esto DEBE dar este resultado". Si no lo da, avisa.
 *
 *  Aquí escribimos nuestra PROPIA función de assert en vez de usar
 *  PHPUnit, que es la herramienta profesional. ¿Por qué?
 *  Porque PHPUnit hay que instalarlo con Composer y configurarlo, y
 *  para entender el CONCEPTO no hace falta. Una vez se entiende esto,
 *  PHPUnit es lo mismo pero con más funciones.
 *
 *  ¿CÓMO SE EJECUTA?
 *    Opción A (navegador): http://localhost/mvp-ventas-ropa/pruebas.php
 *    Opción B (terminal) : php pruebas.php
 *
 *  ¡IMPORTANTE! Este archivo es una HERRAMIENTA DE DESARROLLO. En un
 *  sistema publicado en internet se borra o se bloquea, porque revela
 *  cómo funciona el sistema por dentro.
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */

// Cargamos los archivos que contienen las funciones que vamos a probar.
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/api_dolar.php';
// __DIR__ es una constante mágica de PHP que significa "la carpeta
// donde está este archivo". Se usa para que las rutas funcionen igual
// si el archivo se ejecuta desde el navegador o desde la terminal.


/* =====================================================================
 * INFRAESTRUCTURA DE LAS PRUEBAS
 * =====================================================================
 * Estas tres variables llevan la cuenta de cómo van las pruebas.
 * ===================================================================== */

$total_pruebas   = 0;    // Cuántas pruebas se han corrido
$pruebas_ok      = 0;    // Cuántas pasaron
$pruebas_fallidas = [];  // Los detalles de las que fallaron


/**
 * FUNCIÓN: verificar()
 * ---------------------------------------------------------------------
 * Nuestro assert casero. Es el corazón de todo el archivo.
 *
 *  RECIBE  : $descripcion → qué estamos probando, en español
 *            $esperado    → lo que DEBERÍA devolver
 *            $obtenido    → lo que realmente devolvió
 *  DEVUELVE: nada, pero va acumulando los resultados
 *
 * La idea es tan simple que sorprende: comparar dos valores y contar
 * si coincidieron. Eso es TODA la base de las pruebas automáticas,
 * incluso en las herramientas profesionales.
 */
function verificar(string $descripcion, $esperado, $obtenido): void
{
    // "global" permite modificar variables que están fuera de la
    // función. Normalmente es mejor evitarlo (se prefiere pasar los
    // datos como parámetros), pero aquí mantiene el archivo simple y
    // legible, que es el objetivo didáctico.
    global $total_pruebas, $pruebas_ok, $pruebas_fallidas;

    $total_pruebas++;

    // === es comparación ESTRICTA: compara valor Y tipo.
    //
    // ¿POR QUÉ === Y NO ==?
    // Porque == hace conversiones automáticas y da sorpresas:
    //     "0"  ==  false     → true   (¡confuso!)
    //     "0"  === false     → false  (correcto: uno es texto, otro booleano)
    //
    // En pruebas se usa SIEMPRE ===, porque si una función devuelve
    // el texto "5" cuando debía devolver el número 5, eso ES un error
    // y queremos que la prueba lo detecte.
    if ($esperado === $obtenido) {
        $pruebas_ok++;
    } else {
        // Guardamos el detalle para mostrarlo al final.
        // var_export convierte cualquier valor en texto legible,
        // mostrando también su tipo. Es mejor que print_r para esto
        // porque distingue "5" de 5.
        $pruebas_fallidas[] = [
            'descripcion' => $descripcion,
            'esperado'    => var_export($esperado, true),
            'obtenido'    => var_export($obtenido, true),
        ];
    }
}


/* =====================================================================
 *  GRUPO 1 — PRUEBAS DE limpiar()
 * =====================================================================
 *  Esta función protege contra ataques XSS. Es la más crítica de todas
 *  en seguridad, así que se prueba primero.
 * ===================================================================== */

verificar(
    'limpiar() deja pasar un texto normal sin cambiarlo',
    'Camiseta básica',
    limpiar('Camiseta básica')
);

verificar(
    'limpiar() neutraliza una etiqueta <script> (ataque XSS)',
    '&lt;script&gt;alert(1)&lt;/script&gt;',
    limpiar('<script>alert(1)</script>')
);
// Esta es LA prueba de seguridad del proyecto. Si algún día alguien
// modifica limpiar() y la daña, esta prueba lo detecta de inmediato.

verificar(
    'limpiar() convierte las comillas dobles',
    '&quot;oferta&quot;',
    limpiar('"oferta"')
);

verificar(
    'limpiar() respeta las tildes y la ñ',
    'Pantalón de niño',
    limpiar('Pantalón de niño')
);
// Si el charset estuviera mal configurado, esta prueba fallaría y
// mostraría algo como "PantalÃ³n".

verificar(
    'limpiar() maneja un valor NULL sin romperse',
    '',
    limpiar(null)
);
// Caso borde: el color puede venir NULL de la base de datos. Si
// limpiar() se rompiera con NULL, la lista de prendas fallaría.


/* =====================================================================
 *  GRUPO 2 — PRUEBAS DE formato_pesos()
 * ===================================================================== */

verificar(
    'formato_pesos() pone el separador de miles',
    '$ 45.000',
    formato_pesos(45000)
);

verificar(
    'formato_pesos() funciona con millones',
    '$ 1.250.000',
    formato_pesos(1250000)
);

verificar(
    'formato_pesos() muestra el cero correctamente',
    '$ 0',
    formato_pesos(0)
);
// Caso borde clásico: el cero. Muchas funciones de formato fallan
// justo con el cero.

verificar(
    'formato_pesos() acepta un número que viene como texto',
    '$ 45.000',
    formato_pesos('45000')
);
// Importante: todo lo que sale de MySQL llega a PHP como TEXTO.
// Esta prueba confirma que la función lo maneja.


/* =====================================================================
 *  GRUPO 3 — PRUEBAS DE validar_prenda()
 * =====================================================================
 *  Aquí probamos las reglas del negocio.
 *
 *  Nota la técnica: primero probamos un caso VÁLIDO (que no debe dar
 *  errores) y después cada caso INVÁLIDO por separado. Así, si una
 *  prueba falla, sabemos exactamente cuál regla se dañó.
 * ===================================================================== */

// -- Caso completamente válido: NO debe producir errores --
$prenda_valida = [
    'nombre'     => 'Camiseta básica',
    'talla'      => 'M',
    'color'      => 'Azul',
    'precio'     => '45000',
    'stock'      => '12',
    'imagen_url' => '',
];

verificar(
    'validar_prenda() acepta una prenda correcta (sin errores)',
    0,
    count(validar_prenda($prenda_valida))
);
// Comparamos la CANTIDAD de errores, no el arreglo completo. Así la
// prueba no se rompe si mañana se cambia la redacción de un mensaje.

// -- Nombre vacío --
$sin_nombre = $prenda_valida;
$sin_nombre['nombre'] = '';
verificar(
    'validar_prenda() rechaza una prenda sin nombre',
    1,
    count(validar_prenda($sin_nombre))
);

// -- Nombre con solo espacios --
$espacios = $prenda_valida;
$espacios['nombre'] = '     ';
verificar(
    'validar_prenda() rechaza un nombre que son solo espacios',
    1,
    count(validar_prenda($espacios))
);
// Caso que se le escapa a mucha gente: "   " no es vacío para PHP,
// pero sí debe rechazarse. Por eso la función usa trim().

// -- Talla inventada --
$talla_mala = $prenda_valida;
$talla_mala['talla'] = 'GIGANTE';
verificar(
    'validar_prenda() rechaza una talla que no está en la lista',
    1,
    count(validar_prenda($talla_mala))
);
// Esta prueba demuestra por qué se valida la talla en el servidor
// aunque venga de una lista desplegable: alguien podría manipular
// el navegador y mandar cualquier cosa.

// -- Precio negativo --
$precio_negativo = $prenda_valida;
$precio_negativo['precio'] = '-5000';
verificar(
    'validar_prenda() rechaza un precio negativo',
    1,
    count(validar_prenda($precio_negativo))
);

// -- Precio con letras --
$precio_texto = $prenda_valida;
$precio_texto['precio'] = 'cuarenta mil';
verificar(
    'validar_prenda() rechaza un precio que no es número',
    1,
    count(validar_prenda($precio_texto))
);

// -- Stock con decimales --
$stock_decimal = $prenda_valida;
$stock_decimal['stock'] = '3.5';
verificar(
    'validar_prenda() rechaza un stock con decimales',
    1,
    count(validar_prenda($stock_decimal))
);
// Regla del negocio: no existe media prenda en inventario.

// -- Stock en cero: SÍ debe aceptarse --
$stock_cero = $prenda_valida;
$stock_cero['stock'] = '0';
verificar(
    'validar_prenda() ACEPTA stock en cero (prenda agotada)',
    0,
    count(validar_prenda($stock_cero))
);
// ¡PRUEBA IMPORTANTE! Es fácil escribir la validación de tal forma que
// rechace el cero por error (por ejemplo usando empty(), que considera
// vacío al "0"). Una prenda agotada es perfectamente válida.

// -- URL de imagen inválida --
$url_mala = $prenda_valida;
$url_mala['imagen_url'] = 'esto no es una direccion';
verificar(
    'validar_prenda() rechaza una dirección de imagen inválida',
    1,
    count(validar_prenda($url_mala))
);

// -- URL válida --
$url_buena = $prenda_valida;
$url_buena['imagen_url'] = 'https://ejemplo.com/camiseta.jpg';
verificar(
    'validar_prenda() acepta una dirección de imagen válida',
    0,
    count(validar_prenda($url_buena))
);

// -- Varios errores al tiempo --
$todo_mal = [
    'nombre'     => '',
    'talla'      => 'XXXL',
    'color'      => '',
    'precio'     => '-1',
    'stock'      => 'abc',
    'imagen_url' => '',
];
verificar(
    'validar_prenda() reporta los 4 errores cuando hay varios',
    4,
    count(validar_prenda($todo_mal))
);
// Confirma que la función NO se detiene en el primer error. Si lo
// hiciera, el usuario tendría que corregir de a un campo por vez,
// reenviando el formulario cuatro veces.


/* =====================================================================
 *  GRUPO 4 — PRUEBAS DE etiqueta_stock()
 * =====================================================================
 *  Probamos los LÍMITES de la regla: 0, 5 y 6.
 *  Los errores casi siempre están en los límites, no en el medio.
 *  A eso se le llama "prueba de valores frontera".
 * ===================================================================== */

verificar(
    'etiqueta_stock() marca Agotado cuando el stock es 0',
    true,
    str_contains(etiqueta_stock(0), 'Agotado')
);
// str_contains pregunta si un texto contiene otro. Probamos solo esa
// palabra y no el HTML completo, para que la prueba no se rompa si
// mañana se cambia una clase de CSS.

verificar(
    'etiqueta_stock() avisa en amarillo con stock 5 (el límite)',
    true,
    str_contains(etiqueta_stock(5), 'warning')
);

verificar(
    'etiqueta_stock() marca disponible con stock 6 (pasado el límite)',
    true,
    str_contains(etiqueta_stock(6), 'success')
);
// Estas tres pruebas juntas fijan exactamente dónde está la frontera.
// Si alguien cambia el <= por un < en la función, esta prueba falla.


/* =====================================================================
 *  GRUPO 5 — PRUEBAS DE mensaje_estado()
 * ===================================================================== */

verificar(
    'mensaje_estado() no muestra nada si no llega un estado',
    '',
    mensaje_estado(null)
);

verificar(
    'mensaje_estado() ignora un estado inventado',
    '',
    mensaje_estado('estado_que_no_existe')
);
// ¡PRUEBA DE SEGURIDAD! Confirma que alguien no puede escribir
// consultar.php?estado=<script>... y ver su texto impreso en la página.

verificar(
    'mensaje_estado() muestra el aviso verde al crear',
    true,
    str_contains(mensaje_estado('creado'), 'alert-success')
);


/* =====================================================================
 *  GRUPO 6 — PRUEBAS DE pesos_a_dolares()  (GUÍA 2)
 * ===================================================================== */

verificar(
    'pesos_a_dolares() convierte correctamente',
    'US$ 10.84',
    pesos_a_dolares(45000, 4150.25)
);
// Comprobación manual: 45000 / 4150.25 = 10.842... → 10.84

verificar(
    'pesos_a_dolares() no se rompe si la tasa llega en cero',
    'US$ —',
    pesos_a_dolares(45000, 0)
);
// ¡PRUEBA CRÍTICA! Dividir entre cero en PHP 8 tumba la página entera
// con un error fatal. Esta prueba garantiza que eso nunca pase.

verificar(
    'pesos_a_dolares() maneja el precio en cero',
    'US$ 0.00',
    pesos_a_dolares(0, 4150.25)
);


/* =====================================================================
 *  MOSTRAR EL RESULTADO
 * =====================================================================
 *  El archivo se puede correr desde el navegador o desde la terminal,
 *  así que detectamos dónde estamos para mostrarlo bien en cada caso.
 *
 *  PHP_SAPI dice cómo se está ejecutando PHP. Vale 'cli' cuando se
 *  corre desde la terminal (Command Line Interface).
 * ===================================================================== */

$en_terminal = (PHP_SAPI === 'cli');
$fallidas    = count($pruebas_fallidas);

if ($en_terminal) {

    // ---------- SALIDA PARA LA TERMINAL ----------
    echo "\n";
    echo "=====================================================\n";
    echo "  PRUEBAS · MVP Ventas de Ropa\n";
    echo "=====================================================\n";
    echo "  Total ejecutadas : {$total_pruebas}\n";
    echo "  Pasaron          : {$pruebas_ok}\n";
    echo "  Fallaron         : {$fallidas}\n";
    echo "=====================================================\n";

    if ($fallidas > 0) {
        echo "\nDETALLE DE LAS PRUEBAS QUE FALLARON:\n\n";
        foreach ($pruebas_fallidas as $f) {
            echo "  [X] {$f['descripcion']}\n";
            echo "      Se esperaba : {$f['esperado']}\n";
            echo "      Se obtuvo   : {$f['obtenido']}\n\n";
        }
    } else {
        echo "\n  Todas las pruebas pasaron correctamente.\n\n";
    }

    // exit con un código: 0 significa éxito, 1 significa que hubo
    // fallos. Esto lo usan las herramientas automáticas para saber si
    // el programa está bien sin tener que leer el texto.
    exit($fallidas > 0 ? 1 : 0);
}

// ---------- SALIDA PARA EL NAVEGADOR ----------
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pruebas · MVP Ventas de Ropa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:820px">

    <h1 class="h3 mb-1">Resultado de las pruebas automáticas</h1>
    <p class="text-muted small mb-4">
        MVP Ventas de Ropa &middot; Guía 2 &middot; Casos de prueba con assert() casero
    </p>

    <!-- Tarjeta grande de resumen: verde si todo pasó, roja si no -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-4">
            <?php if ($fallidas === 0): ?>
                <p class="display-6 text-success mb-2">Todas las pruebas pasaron</p>
                <p class="text-muted mb-0">
                    <?= $pruebas_ok ?> de <?= $total_pruebas ?> verificaciones correctas
                </p>
            <?php else: ?>
                <p class="display-6 text-danger mb-2">
                    <?= $fallidas ?> prueba(s) fallaron
                </p>
                <p class="text-muted mb-0">
                    <?= $pruebas_ok ?> de <?= $total_pruebas ?> verificaciones correctas
                </p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($fallidas > 0): ?>
        <div class="card border-danger shadow-sm mb-4">
            <div class="card-header bg-danger text-white">
                Detalle de las pruebas que fallaron
            </div>
            <div class="card-body">
                <?php foreach ($pruebas_fallidas as $f): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <p class="fw-semibold mb-1"><?= limpiar($f['descripcion']) ?></p>
                        <p class="small mb-0">
                            <span class="text-muted">Se esperaba:</span>
                            <code><?= limpiar($f['esperado']) ?></code>
                        </p>
                        <p class="small mb-0">
                            <span class="text-muted">Se obtuvo:</span>
                            <code class="text-danger"><?= limpiar($f['obtenido']) ?></code>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body small">
            <h2 class="h6">¿Qué se probó aquí?</h2>
            <ul class="mb-0">
                <li><strong>limpiar()</strong> — que neutralice los ataques XSS y respete las tildes</li>
                <li><strong>formato_pesos()</strong> — que muestre bien los miles y el cero</li>
                <li><strong>validar_prenda()</strong> — las reglas del negocio, incluidos los casos borde</li>
                <li><strong>etiqueta_stock()</strong> — los límites exactos de la alerta de inventario</li>
                <li><strong>mensaje_estado()</strong> — que ignore estados inventados</li>
                <li><strong>pesos_a_dolares()</strong> — la conversión y la división por cero</li>
            </ul>
        </div>
    </div>

    <p class="text-center mt-4">
        <a href="consultar.php" class="btn btn-dark btn-sm">Volver al sistema</a>
    </p>

</div>
</body>
</html>
