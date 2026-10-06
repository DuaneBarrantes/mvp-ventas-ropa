<?php
/**
 * =====================================================================
 *  ARCHIVO  : estadisticas.php
 *  PROYECTO : MVP Ventas de Ropa
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTA PÁGINA?
 *  Esta es la VISTA DE CONSULTA PROPIA del proyecto: lo que diferencia
 *  a Ventas de Ropa de los otros tres proyectos del salón.
 *
 *  Todos los equipos hicieron el mismo CRUD, pero cada uno tiene su
 *  consulta característica según su negocio. La nuestra responde
 *  preguntas de una tienda de ropa:
 *     - ¿Cuánta plata tengo invertida en inventario?
 *     - ¿Qué tallas se me están agotando?
 *     - ¿Cuáles prendas hay que pedir ya?
 *
 *  CONCEPTO CLAVE DE ESTA PÁGINA: las FUNCIONES DE AGREGACIÓN de SQL.
 *
 *  Una función de agregación toma MUCHAS filas y devuelve UN solo
 *  resultado. Las principales son:
 *      COUNT() → cuenta cuántas filas hay
 *      SUM()   → suma los valores de una columna
 *      AVG()   → saca el promedio
 *      MAX()   → el valor más alto
 *      MIN()   → el valor más bajo
 *
 *  ¿POR QUÉ HACER LAS CUENTAS EN SQL Y NO EN PHP?
 *  Se podría traer todas las filas y sumarlas con un foreach en PHP.
 *  Pero la base de datos está OPTIMIZADA para eso. Si hubiera 50.000
 *  prendas, PHP tendría que cargarlas todas en memoria; MySQL en cambio
 *  hace la suma internamente y devuelve un solo número.
 *
 *  REGLA: las cuentas sobre muchos datos se hacen en la base de datos.
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
 * ¿POR QUÉ ESTA PÁGINA USA query() Y NO prepare()?
 * =====================================================================
 * Es una pregunta buena, y merece respuesta, porque en TODO el resto del
 * proyecto se usan sentencias preparadas y aquí no.
 *
 * La razón: prepare() + bind_param() existen para una sola cosa —
 * separar la ORDEN de los DATOS, para que un dato del usuario no se
 * pueda convertir en una instrucción. Eso es lo que impide la inyección
 * SQL.
 *
 * Las consultas de esta página NO RECIBEN NINGÚN DATO DEL USUARIO. Son
 * textos fijos, escritos por nosotros, sin un solo signo ? y sin una
 * sola variable pegada adentro. Míralas: no hay nada que venga de $_GET,
 * de $_POST ni de ningún formulario. Si no entra nada de fuera, no hay
 * nada que inyectar.
 *
 * ¡PERO OJO CON LA REGLA! El día que alguien le agregue un filtro a esta
 * página — por ejemplo "muéstrame solo el mes que yo escoja" — ese dato
 * SÍ vendría del usuario, y entonces esa consulta TIENE que pasarse a
 * prepare() + bind_param(). No es opcional.
 *
 * Regla para recordar: consulta con datos de fuera → preparada, siempre.
 * Consulta de texto fijo → query() está bien, y es más corta de leer.
 * En caso de duda, preparada: no cuesta casi nada y nunca sobra.
 * ===================================================================== */


/* =====================================================================
 * CONSULTA 1 — RESUMEN GENERAL
 * =====================================================================
 * Una sola consulta que trae varios totales de golpe.
 *
 * Fíjate en el uso de AS: le pone un "apodo" al resultado para poder
 * leerlo después como $resumen['total_prendas'].
 * Sin el AS, la columna se llamaría literalmente "COUNT(*)", que es
 * incomodísimo de usar en PHP.
 *
 * Esta consulta NO lleva datos del usuario, por eso no tiene huecos (?)
 * ni necesita bind_param. Se puede usar query() directo.
 * ===================================================================== */
$sql_resumen = '
    SELECT
        COUNT(*)                        AS total_prendas,
        COALESCE(SUM(stock), 0)         AS total_unidades,
        COALESCE(SUM(precio * stock), 0) AS valor_total,
        COALESCE(AVG(precio), 0)        AS precio_promedio,
        COALESCE(MAX(precio), 0)        AS precio_maximo,
        COALESCE(MIN(precio), 0)        AS precio_minimo
    FROM prendas';

/*
 * ¿QUÉ ES COALESCE Y POR QUÉ ESTÁ AHÍ?
 *
 * Es una protección importante. Cuando la tabla está VACÍA:
 *     COUNT(*) devuelve 0   ← bien
 *     SUM(stock) devuelve NULL  ← ¡problema!
 *
 * Porque "la suma de nada" no es cero para SQL, es "desconocido".
 * Si ese NULL llega a PHP e intentamos formatearlo o dividirlo,
 * aparecen advertencias o resultados raros.
 *
 * COALESCE(valor, respaldo) significa: "usa el valor; si es NULL,
 * usa el respaldo". Así garantizamos que siempre llegue un número.
 *
 * Este es un caso típico de algo que funciona perfecto mientras hay
 * datos y se rompe el día que la tabla amanece vacía.
 */

$resumen = $conexion->query($sql_resumen)->fetch_assoc();


/* =====================================================================
 * CONSULTA 2 — PRENDAS AGRUPADAS POR TALLA
 * =====================================================================
 * Aquí entra GROUP BY, que es el compañero de las funciones de
 * agregación.
 *
 * SIN GROUP BY:  COUNT(*) cuenta TODAS las filas de la tabla.
 * CON GROUP BY talla: primero separa las filas en montoncitos (uno por
 * cada talla) y después cuenta CADA montoncito por separado.
 *
 * Es como repartir las prendas en cajas según la talla y después
 * contar cuántas quedaron en cada caja.
 * ===================================================================== */
$sql_tallas = '
    SELECT
        talla,
        COUNT(*)                AS cantidad_modelos,
        COALESCE(SUM(stock), 0) AS unidades
    FROM prendas
    GROUP BY talla
    ORDER BY unidades DESC';
// ORDER BY unidades DESC = la talla con más unidades aparece primero

$tallas = $conexion->query($sql_tallas)->fetch_all(MYSQLI_ASSOC);


/* =====================================================================
 * CONSULTA 3 — PRENDAS POR AGOTARSE
 * =====================================================================
 * Esta es la consulta más ÚTIL del negocio: dice qué hay que pedir.
 *
 * WHERE stock <= 5 filtra antes de mostrar.
 *
 * ¿CUÁL ES LA DIFERENCIA ENTRE WHERE Y HAVING? (pregunta de examen)
 *     WHERE  filtra las filas ANTES de agrupar
 *     HAVING filtra los grupos DESPUÉS de agrupar
 * Aquí no agrupamos, entonces va WHERE.
 * ===================================================================== */
$sql_agotandose = '
    SELECT id, nombre, talla, color, precio, stock
    FROM prendas
    WHERE stock <= 5
    ORDER BY stock ASC, nombre ASC';
// ORDER BY con DOS criterios: primero por stock de menor a mayor (ASC),
// y si dos prendas tienen el mismo stock, se desempata por nombre
// alfabéticamente. Así el orden es siempre predecible.

$agotandose = $conexion->query($sql_agotandose)->fetch_all(MYSQLI_ASSOC);


/* =====================================================================
 * CONSULTA 4 — LAS 5 PRENDAS MÁS CARAS
 * =====================================================================
 * LIMIT 5 = "tráeme solo las primeras 5 filas del resultado".
 * Combinado con ORDER BY precio DESC, da "las 5 más caras".
 * ===================================================================== */
$sql_caras = '
    SELECT nombre, talla, precio, stock
    FROM prendas
    ORDER BY precio DESC
    LIMIT 5';

$mas_caras = $conexion->query($sql_caras)->fetch_all(MYSQLI_ASSOC);


$titulo = 'Estadísticas';
require_once 'includes/cabecera.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Estadísticas del inventario</h1>
        <p class="text-muted small mb-0">
            Consulta propia del proyecto Ventas de Ropa
        </p>
    </div>
    <a href="consultar.php" class="btn btn-sm btn-outline-secondary">
        &larr; Volver al inventario
    </a>
</div>


<!-- =================================================================
     TARJETAS DE RESUMEN
     ================================================================= -->
<div class="row g-3 mb-4">

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Modelos registrados</p>
                <p class="h3 mb-0"><?= (int) $resumen['total_prendas'] ?></p>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Unidades en bodega</p>
                <p class="h3 mb-0"><?= (int) $resumen['total_unidades'] ?></p>
                <!-- Nota conceptual: "modelos" y "unidades" son cosas
                     distintas. 3 modelos con 10 unidades cada uno son
                     3 modelos y 30 unidades. Confundirlos es un error
                     clásico al interpretar un inventario. -->
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Valor del inventario</p>
                <p class="h4 mb-0 text-success">
                    <?= formato_pesos($resumen['valor_total']) ?>
                </p>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-1">Precio promedio</p>
                <p class="h4 mb-0">
                    <?= formato_pesos($resumen['precio_promedio']) ?>
                </p>
                <p class="text-muted small mb-0 mt-1">
                    Entre <?= formato_pesos($resumen['precio_minimo']) ?>
                    y <?= formato_pesos($resumen['precio_maximo']) ?>
                </p>
            </div>
        </div>
    </div>

</div>


<div class="row g-4">

    <!-- ============ DISTRIBUCIÓN POR TALLA ============ -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 mb-3">Distribución por talla</h2>

                <?php if (empty($tallas)): ?>
                    <p class="text-muted small mb-0">Todavía no hay datos.</p>
                <?php else: ?>

                    <?php
                    /* ------------------------------------------------
                     * Para dibujar las barras necesitamos saber cuál
                     * es la talla con más unidades, y así calcular el
                     * porcentaje de ancho de cada barra.
                     *
                     * array_column() saca una sola columna del arreglo:
                     * de [['talla'=>'M','unidades'=>20], ...]
                     * saca [20, ...]
                     *
                     * max() devuelve el mayor de esos números.
                     * ------------------------------------------------ */
                    $max_unidades = max(array_column($tallas, 'unidades'));

                    // Protección contra división por cero: si todas las
                    // tallas tienen 0 unidades, max() daría 0 y dividir
                    // entre 0 rompería la página.
                    if ($max_unidades == 0) {
                        $max_unidades = 1;
                    }
                    ?>

                    <?php foreach ($tallas as $t): ?>
                        <?php
                        // Regla de tres para el ancho de la barra:
                        // si la talla con más unidades ocupa el 100%,
                        // ¿cuánto ocupa esta?
                        $porcentaje = ($t['unidades'] / $max_unidades) * 100;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>
                                    <strong>Talla <?= limpiar($t['talla']) ?></strong>
                                    <span class="text-muted">
                                        &middot; <?= (int) $t['cantidad_modelos'] ?> modelo(s)
                                    </span>
                                </span>
                                <span class="text-muted">
                                    <?= (int) $t['unidades'] ?> unidades
                                </span>
                            </div>

                            <!-- Barra de progreso de Bootstrap.
                                 El ancho se escribe en el style con el
                                 porcentaje calculado arriba.

                                 round(..., 1) redondea a un decimal
                                 para que no salga 33.333333333% -->
                            <div class="progress" style="height:10px">
                                <div class="progress-bar bg-dark"
                                     role="progressbar"
                                     style="width: <?= round($porcentaje, 1) ?>%"
                                     aria-valuenow="<?= (int) $t['unidades'] ?>"
                                     aria-valuemin="0"
                                     aria-valuemax="<?= (int) $max_unidades ?>"></div>
                            </div>
                            <!-- Los atributos aria-* describen la barra
                                 para los lectores de pantalla de
                                 personas con discapacidad visual. Una
                                 barra sin ellos es invisible para esos
                                 usuarios. -->
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>


    <!-- ============ PRENDAS POR AGOTARSE ============ -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 mb-1">Prendas por agotarse</h2>
                <p class="text-muted small mb-3">
                    Con 5 unidades o menos
                </p>

                <?php if (empty($agotandose)): ?>
                    <div class="alert alert-success small mb-0">
                        Ninguna prenda está por agotarse. El inventario está bien surtido.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Prenda</th>
                                    <th>Talla</th>
                                    <th class="text-center">Stock</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($agotandose as $a): ?>
                                <tr>
                                    <td class="small"><?= limpiar($a['nombre']) ?></td>
                                    <td class="small"><?= limpiar($a['talla']) ?></td>
                                    <td class="text-center">
                                        <?= etiqueta_stock((int) $a['stock']) ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="editar.php?id=<?= (int) $a['id'] ?>"
                                           class="btn btn-sm btn-outline-primary py-0">
                                            Reabastecer
                                        </a>
                                        <!-- Un botón de acción DIRECTA
                                             desde el reporte. Así el
                                             usuario no tiene que ir a
                                             buscar la prenda a la lista:
                                             ve el problema y lo
                                             resuelve en un clic. -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <!-- ============ LAS 5 MÁS CARAS ============ -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6 mb-3">Las 5 prendas más costosas</h2>

                <?php if (empty($mas_caras)): ?>
                    <p class="text-muted small mb-0">Todavía no hay datos.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px">#</th>
                                    <th>Prenda</th>
                                    <th>Talla</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-center">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            // $posicion es un contador manual para
                            // numerar las filas: 1, 2, 3...
                            // No se puede usar el id de la prenda para
                            // esto, porque el id no es consecutivo (si
                            // se borra la prenda 2, los ids quedan
                            // 1, 3, 4...).
                            $posicion = 1;
                            foreach ($mas_caras as $c):
                            ?>
                                <tr>
                                    <td class="text-muted"><?= $posicion ?></td>
                                    <td><?= limpiar($c['nombre']) ?></td>
                                    <td><?= limpiar($c['talla']) ?></td>
                                    <td class="text-end fw-semibold">
                                        <?= formato_pesos($c['precio']) ?>
                                    </td>
                                    <td class="text-center"><?= (int) $c['stock'] ?></td>
                                </tr>
                            <?php
                                // Aumentamos el contador en 1 para la
                                // siguiente vuelta del bucle.
                                // $posicion++ es lo mismo que escribir
                                // $posicion = $posicion + 1
                                $posicion++;
                            endforeach;
                            ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php require_once 'includes/pie.php'; ?>
