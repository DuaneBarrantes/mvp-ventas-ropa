<?php
/**
 * =====================================================================
 *  ARCHIVO  : eliminar.php
 *  PROYECTO : MVP Ventas de Ropa
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTA PÁGINA?
 *  Es la "D" del CRUD: DELETE (eliminar). Borra una prenda.
 *
 *  ¡PERO NO BORRA DE UNA! Primero MUESTRA la prenda y pide confirmación.
 *
 *  ¿POR QUÉ DOS PASOS Y NO UNO?
 *  Por dos razones, y las dos son importantes para la sustentación:
 *
 *  1. SEGURIDAD DEL USUARIO: borrar es irreversible. Un clic mal dado
 *     no puede costar un registro. Se le muestra qué va a borrar y se
 *     le pide que confirme.
 *
 *  2. SEGURIDAD TÉCNICA: una acción que MODIFICA la base de datos nunca
 *     debe ejecutarse con un simple enlace (método GET). Los
 *     navegadores precargan enlaces, los antivirus los visitan, los
 *     buscadores los rastrean. Si eliminar.php?id=3 borrara de una,
 *     bastaría con que un programa visitara el enlace para perder el
 *     dato. Por eso el borrado real solo ocurre por POST.
 *
 *  ¿QUÉ RECIBE?
 *     Por GET  : el id, para mostrar la confirmación
 *     Por POST : el id, para ejecutar el borrado
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
 * PARTE 1 — SI LLEGA POR POST: BORRAR DE VERDAD
 * ===================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // El id viene del campo oculto del formulario de confirmación
    $id = (int) ($_POST['id'] ?? 0);

    if ($id > 0) {

        // --------------------------------------------------------
        // CONSULTA PREPARADA — DELETE
        //
        // DELETE FROM tabla WHERE condición
        //
        // ¡¡EL WHERE ES OBLIGATORIO!!
        // Si se escribiera solo "DELETE FROM prendas" sin WHERE, se
        // borraría la tabla ENTERA, todas las filas, sin aviso y sin
        // vuelta atrás.
        //
        // Este es el error más costoso que se puede cometer con SQL.
        // Antes de ejecutar cualquier DELETE o UPDATE, siempre se
        // revisa dos veces que tenga su WHERE.
        //
        // Un truco profesional: antes de correr un DELETE, se escribe
        // primero como SELECT con el mismo WHERE. Si el SELECT muestra
        // exactamente lo que se quiere borrar, ahí sí se cambia la
        // palabra SELECT por DELETE.
        // --------------------------------------------------------
        $consulta = $conexion->prepare('DELETE FROM prendas WHERE id = ?');
        $consulta->bind_param('i', $id);
        $consulta->execute();
        $consulta->close();
    }

    // Volvemos al inventario con el aviso de éxito
    header('Location: consultar.php?estado=eliminado');
    exit;
}


/* =====================================================================
 * PARTE 2 — SI LLEGA POR GET: MOSTRAR LA CONFIRMACIÓN
 * ===================================================================== */

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: consultar.php?estado=no_existe');
    exit;
}

// Buscamos la prenda para poder mostrarle al usuario QUÉ va a borrar.
// Mostrar solo "¿Seguro que quiere eliminar?" sin decir qué, es mala
// práctica: el usuario no puede confirmar lo que no ve.
$consulta = $conexion->prepare(
    'SELECT id, nombre, talla, color, precio, stock
     FROM prendas
     WHERE id = ?
     LIMIT 1'
);
$consulta->bind_param('i', $id);
$consulta->execute();

$prenda = $consulta->get_result()->fetch_assoc();
$consulta->close();

if ($prenda === null) {
    header('Location: consultar.php?estado=no_existe');
    exit;
}

$titulo = 'Eliminar prenda';
require_once 'includes/cabecera.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">

        <!-- border-danger pinta el borde rojo: señal visual de que
             esta es una acción peligrosa e irreversible -->
        <div class="card border-danger shadow-sm">

            <div class="card-header bg-danger text-white">
                <h1 class="h5 mb-0">Confirmar eliminación</h1>
            </div>

            <div class="card-body p-4">

                <p class="mb-4">
                    Vas a eliminar esta prenda del inventario.
                    <strong>Esta acción no se puede deshacer.</strong>
                </p>

                <!-- Mostramos los datos de la prenda para que el
                     usuario confirme que es la correcta -->
                <div class="table-responsive mb-4">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted" style="width:120px">Nombre</th>
                                <td><strong><?= limpiar($prenda['nombre']) ?></strong></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Talla</th>
                                <td><?= limpiar($prenda['talla']) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Color</th>
                                <td><?= limpiar($prenda['color']) ?: '—' ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Precio</th>
                                <td><?= formato_pesos($prenda['precio']) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Stock</th>
                                <td><?= (int) $prenda['stock'] ?> unidad(es)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ---------------------------------------------------
                     FORMULARIO DE CONFIRMACIÓN

                     method="post" — aquí está la clave de seguridad que
                     se explicó arriba: el borrado solo ocurre por POST,
                     nunca por un enlace.
                     --------------------------------------------------- -->
                <form method="post" action="eliminar.php">

                    <!-- Campo OCULTO.
                         type="hidden" significa que el usuario no lo ve
                         en pantalla, pero sí viaja con el formulario.
                         Es la forma de pasarle el id al servidor sin
                         mostrárselo al usuario.

                         OJO: "oculto" no significa "seguro". El usuario
                         podría cambiarlo con las herramientas del
                         navegador. Por eso el servidor SIEMPRE vuelve a
                         validar el id que le llega. -->
                    <input type="hidden" name="id" value="<?= (int) $prenda['id'] ?>">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            Sí, eliminar
                        </button>
                        <!-- El botón de cancelar es un enlace normal:
                             solo devuelve al inventario sin hacer nada -->
                        <a href="consultar.php" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>

            </div>
        </div>

        <p class="text-center text-muted small mt-3">
            Si no estás seguro, pulsa Cancelar. No se borrará nada.
        </p>

    </div>
</div>

<?php require_once 'includes/pie.php'; ?>
