<?php
/**
 * =====================================================================
 *  ARCHIVO  : consultar.php
 *  PROYECTO : MVP Ventas de Ropa
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTA PÁGINA?
 *  Es la pantalla principal del sistema: muestra la lista de prendas y
 *  permite BUSCAR por nombre o por color.
 *
 *  Cumple la "R" del CRUD. Así se reparten las cuatro operaciones entre
 *  los archivos del proyecto:
 *
 *      C - Create  (Crear)      → registrar.php  + guardar.php
 *      R - Read    (LEER)       → ESTA PÁGINA
 *      U - Update  (Actualizar) → editar.php     + actualizar.php
 *      D - Delete  (Eliminar)   → eliminar.php
 *
 *  ¿POR QUÉ CREAR Y ACTUALIZAR LLEVAN DOS ARCHIVOS?
 *  Porque cada archivo hace UNA sola cosa: uno muestra el formulario,
 *  el otro escribe en la base de datos. Así, cuando algo falla, se sabe
 *  de una vez dónde buscar. Es la misma regla que se aplicó a las
 *  carpetas: la de conexión solo conecta, la de páginas solo muestra.
 *
 *  Esta página solo LEE, por eso no necesita un segundo archivo.
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */

require_once 'includes/sesion.php';

// ¡EL PORTERO! Esta línea protege la página.
// Si alguien escribe la dirección sin haber iniciado sesión, lo
// devuelve al login antes de mostrar un solo dato.
exigir_sesion();

require_once 'includes/funciones.php';
require_once 'config/conexion.php';


/* =====================================================================
 * 1. RECOGER EL TÉRMINO DE BÚSQUEDA
 * =====================================================================
 * La búsqueda viaja por la dirección:  consultar.php?buscar=camisa
 * Por eso se lee de $_GET y no de $_POST.
 *
 * ¿POR QUÉ GET Y NO POST PARA BUSCAR?
 * Porque así la búsqueda se puede guardar en favoritos, compartir por
 * WhatsApp, o volver a ella con el botón "atrás" del navegador. Regla
 * general: GET para consultar, POST para modificar datos.
 * ===================================================================== */
$buscar = trim($_GET['buscar'] ?? '');


/* =====================================================================
 * 2. CONSULTAR LA BASE DE DATOS
 * =====================================================================
 * Hay dos casos distintos, por eso el if:
 *   - Si el usuario buscó algo → traemos solo lo que coincide
 *   - Si no buscó nada         → traemos todo
 * ===================================================================== */

if ($buscar !== '') {

    // -------- CASO A: hay búsqueda --------
    //
    // LIKE sirve para buscar "parecidos", no coincidencias exactas.
    // El signo % es un comodín que significa "aquí puede ir cualquier
    // cosa". Entonces:
    //     %camisa%  encuentra "Camisa a cuadros" y "Mi camisa vieja"
    //     camisa%   encontraría solo las que EMPIEZAN por camisa
    //
    // Buscamos en dos columnas (nombre y color) unidas con OR, para que
    // el usuario pueda buscar tanto "camisa" como "azul".

    $sql = 'SELECT id, nombre, talla, color, precio, stock, imagen_url
            FROM prendas
            WHERE nombre LIKE ? OR color LIKE ?
            ORDER BY creado_en DESC';
    // ORDER BY creado_en DESC = ordena por fecha de creación, de la más
    // nueva a la más vieja (DESC = descendente). Así lo último que se
    // registró aparece de primero.

    $consulta = $conexion->prepare($sql);

    // Armamos el patrón de búsqueda con los comodines.
    //
    // ¡IMPORTANTE! Los % se agregan AQUÍ, en la variable de PHP, y NO
    // dentro del texto de la consulta. Si escribiéramos LIKE '%?%' la
    // consulta preparada no funcionaría, porque el ? quedaría dentro de
    // un texto entre comillas y MySQL lo leería como el símbolo ?,
    // no como un hueco para rellenar.
    $patron = '%' . $buscar . '%';

    // Dos signos ? en la consulta = dos letras en bind_param = "ss"
    // (los dos son texto). Y se manda la misma variable dos veces.
    $consulta->bind_param('ss', $patron, $patron);

} else {

    // -------- CASO B: no hay búsqueda, traer todo --------
    //
    // Esta consulta NO lleva datos del usuario, así que no tiene huecos
    // (?) y no necesita bind_param.
    $sql = 'SELECT id, nombre, talla, color, precio, stock, imagen_url
            FROM prendas
            ORDER BY creado_en DESC';

    $consulta = $conexion->prepare($sql);
}

// Ejecutar y recoger resultados (esto es igual en ambos casos)
$consulta->execute();
$resultado = $consulta->get_result();

// fetch_all(MYSQLI_ASSOC) trae TODAS las filas de una vez, como un
// arreglo de arreglos. Es cómodo cuando son pocos registros.
//
// NOTA PARA LA SUSTENTACIÓN: si la tabla tuviera miles de registros,
// esto cargaría todo en memoria de golpe. Ahí se usaría paginación
// (LIMIT y OFFSET) para traer de 20 en 20. Para nuestro MVP no hace
// falta, pero es bueno saberlo.
$prendas = $resultado->fetch_all(MYSQLI_ASSOC);

$consulta->close();

// count() cuenta cuántos elementos tiene el arreglo
$total = count($prendas);


// Título para la cabecera y arranque del HTML
$titulo = 'Inventario';
require_once 'includes/cabecera.php';
?>

<!-- =================================================================
     ENCABEZADO DE LA PÁGINA
     d-flex          = pone los elementos en fila
     justify-content-between = uno a la izquierda y otro a la derecha
     ================================================================= -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Inventario de prendas</h1>
        <p class="text-muted mb-0 small">
            <?php if ($buscar !== ''): ?>
                <!-- Si hubo búsqueda, informamos qué se buscó y cuántos
                     resultados hubo. Nunca se deja al usuario sin saber
                     por qué ve pocas filas. -->
                <?= $total ?> resultado(s) para
                &ldquo;<strong><?= limpiar($buscar) ?></strong>&rdquo;
            <?php else: ?>
                <?= $total ?> prenda(s) registrada(s)
            <?php endif; ?>
        </p>
    </div>

    <a href="registrar.php" class="btn btn-dark">
        + Registrar prenda
    </a>
</div>


<!-- =================================================================
     FORMULARIO DE BÚSQUEDA
     method="get" para que la búsqueda quede en la dirección
     ================================================================= -->
<form method="get" action="consultar.php" class="row g-2 mb-4">
    <div class="col-sm-8 col-md-6">
        <input type="search"
               name="buscar"
               class="form-control"
               placeholder="Buscar por nombre o color..."
               value="<?= limpiar($buscar) ?>">
        <!-- El value devuelve el texto buscado para que quede escrito
             en la caja después de buscar. Si no, el usuario no sabría
             qué escribió. -->
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-dark">Buscar</button>
    </div>

    <!-- El botón "Limpiar" solo aparece si hay una búsqueda activa.
         Es simplemente un enlace a consultar.php sin parámetros. -->
    <?php if ($buscar !== ''): ?>
        <div class="col-auto">
            <a href="consultar.php" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    <?php endif; ?>
</form>


<!-- =================================================================
     TABLA DE RESULTADOS
     ================================================================= -->
<?php if ($total === 0): ?>

    <!-- CASO SIN RESULTADOS.
         Nunca se deja la pantalla en blanco: se explica qué pasó y
         qué puede hacer el usuario. Eso se llama "estado vacío". -->
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <h2 class="h5 text-muted">
                <?= $buscar !== '' ? 'No se encontraron prendas' : 'Todavía no hay prendas registradas' ?>
            </h2>
            <p class="text-muted small mb-3">
                <?php if ($buscar !== ''): ?>
                    Intenta con otra palabra, o revisa que esté bien escrita.
                <?php else: ?>
                    Registra la primera prenda para empezar a usar el sistema.
                <?php endif; ?>
            </p>
            <a href="<?= $buscar !== '' ? 'consultar.php' : 'registrar.php' ?>" class="btn btn-dark btn-sm">
                <?= $buscar !== '' ? 'Ver todas las prendas' : 'Registrar la primera prenda' ?>
            </a>
        </div>
    </div>

<?php else: ?>

    <div class="card border-0 shadow-sm">
        <!-- table-responsive hace que en el celular la tabla se pueda
             deslizar de lado en vez de dañar el diseño -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Prenda</th>
                        <th>Talla</th>
                        <th>Color</th>
                        <th class="text-end">Precio</th>
                        <th class="text-center">Stock</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                /* ---------------------------------------------------
                 * EL RECORRIDO (bucle foreach)
                 * ---------------------------------------------------
                 * foreach recorre el arreglo $prendas y en cada vuelta
                 * mete una fila distinta en la variable $p.
                 *
                 * O sea: se escribe UNA sola fila de tabla en el código,
                 * y PHP la repite tantas veces como prendas haya.
                 * --------------------------------------------------- */
                foreach ($prendas as $p):
                ?>
                    <tr>
                        <td>
                            <!-- limpiar() en TODO dato que venga de la
                                 base de datos. Es la regla de oro. -->
                            <strong><?= limpiar($p['nombre']) ?></strong>
                        </td>

                        <td>
                            <span class="badge text-bg-secondary">
                                <?= limpiar($p['talla']) ?>
                            </span>
                        </td>

                        <td>
                            <!-- El color puede venir vacío (NULL en la
                                 base). El operador ?: (llamado Elvis)
                                 dice: "si hay algo úsalo; si no, pon un
                                 guion". Así no queda una celda vacía. -->
                            <?= limpiar($p['color']) ?: '<span class="text-muted">—</span>' ?>
                        </td>

                        <td class="text-end">
                            <!-- formato_pesos convierte 45000 en $ 45.000 -->
                            <?= formato_pesos($p['precio']) ?>
                        </td>

                        <td class="text-center">
                            <!-- etiqueta_stock pinta verde, amarillo o
                                 rojo según la cantidad -->
                            <?= etiqueta_stock((int) $p['stock']) ?>
                        </td>

                        <td class="text-end text-nowrap">
                            <!-- Los enlaces llevan el id de la prenda en
                                 la dirección: detalle.php?id=3
                                 Así la página de destino sabe cuál
                                 prenda debe mostrar.

                                 urlencode() protege el valor por si
                                 llegara a traer caracteres raros. -->
                            <a href="detalle.php?id=<?= urlencode($p['id']) ?>"
                               class="btn btn-sm btn-outline-secondary">Ver</a>

                            <a href="editar.php?id=<?= urlencode($p['id']) ?>"
                               class="btn btn-sm btn-outline-primary">Editar</a>

                            <!-- ELIMINAR — con DOS barreras de seguridad.

                                 BARRERA 1 (aquí): el onclick con
                                 confirm() saca la ventanita del
                                 navegador "¿Seguro que quieres
                                 eliminar?". Si el usuario pulsa
                                 Cancelar, confirm() devuelve false y el
                                 "return false" impide que el enlace se
                                 abra siquiera.

                                 BARRERA 2 (en eliminar.php): una página
                                 de confirmación que muestra CUÁL prenda
                                 se va a borrar, y donde el borrado real
                                 solo ocurre por POST.

                                 ¿Por qué dos y no una?
                                 Porque la primera se puede saltar. Si
                                 alguien desactiva JavaScript, el
                                 confirm() no aparece y el enlace se
                                 abre directo. Es el mismo razonamiento
                                 de la validación: lo del navegador es
                                 comodidad, lo del servidor es seguridad.

                                 Además, una acción que MODIFICA datos no
                                 debería viajar por un enlace (GET): los
                                 navegadores precargan enlaces y los
                                 buscadores los rastrean. Por eso el
                                 DELETE de verdad exige un POST. -->
                            <a href="eliminar.php?id=<?= urlencode($p['id']) ?>"
                               onclick="return confirm('¿Seguro que quieres eliminar esta prenda?')"
                               class="btn btn-sm btn-outline-danger">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>

<?php
// Cerramos el HTML con el pie común
require_once 'includes/pie.php';
?>
