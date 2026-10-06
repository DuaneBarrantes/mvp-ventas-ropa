<?php
/**
 * =====================================================================
 *  ARCHIVO  : editar.php
 *  PROYECTO : MVP Ventas de Ropa
 *  OPERACIÓN: UPDATE (actualizar) — parte 1 de 2
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTE ARCHIVO?
 *  UNA sola cosa: mostrar el formulario de edición YA LLENO con los
 *  datos actuales de la prenda. A eso se le llama "formulario
 *  precargado".
 *
 *  Este archivo NO modifica nada. Solo consulta la prenda y la dibuja.
 *  Cuando el usuario pulsa "Guardar cambios", los datos se van a
 *  actualizar.php, que es el que sí aplica el UPDATE.
 *
 *  ¿EN QUÉ SE DIFERENCIA DE registrar.php?
 *  En que registrar.php abre el formulario VACÍO, y este lo abre LLENO.
 *  Todo lo demás (los campos, las reglas, el diseño) es idéntico.
 *
 *  ¿QUÉ RECIBE? El id de la prenda por la dirección: editar.php?id=3
 *  ¿QUÉ ENTREGA? El formulario precargado en pantalla.
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
 * 1. RECIBIR Y VALIDAR EL ID
 * =====================================================================
 * El id llega por la dirección. NUNCA se confía en él tal como viene:
 * alguien podría escribir  editar.php?id=abc  o  editar.php?id=-5
 *
 * (int) convierte a número entero. Si llega "abc" se vuelve 0; si
 * llega "3" se vuelve 3. Esta conversión es, por sí sola, una defensa
 * contra inyección SQL para valores numéricos.
 * ===================================================================== */
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: consultar.php?estado=no_existe');
    exit;
}


/* =====================================================================
 * 2. BUSCAR LA PRENDA EN LA BASE DE DATOS
 * ===================================================================== */
$stmt = $conexion->prepare(
    'SELECT id, nombre, talla, color, precio, stock, imagen_url
     FROM prendas
     WHERE id = ?
     LIMIT 1'
);

// Un solo hueco (?), tipo "i" porque el id es un número entero
$stmt->bind_param('i', $id);
$stmt->execute();

$prenda = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Si no existe (por ejemplo, alguien la borró desde otro computador
// mientras este usuario tenía la lista abierta), avisamos y salimos.
if ($prenda === null) {
    header('Location: consultar.php?estado=no_existe');
    exit;
}


/* =====================================================================
 * 3. DECIDIR QUÉ SE MUESTRA EN EL FORMULARIO
 * =====================================================================
 * Hay dos casos:
 *
 *   A) El usuario acaba de pulsar "Editar"
 *      → se muestran los datos que están en la base de datos
 *
 *   B) El usuario ya intentó guardar y actualizar.php lo devolvió por
 *      un error de validación
 *      → se muestra lo que ÉL escribió, no lo de la base, para que no
 *        pierda su trabajo y solo corrija el campo malo
 *
 * Por eso primero miramos si hay datos guardados en la sesión.
 * (La técnica está explicada con detalle en registrar.php.)
 * ===================================================================== */

$errores   = $_SESSION['errores_formulario'] ?? [];
$devueltos = $_SESSION['datos_formulario']   ?? [];

// Leídos y botados, para que no reaparezcan la próxima vez
unset($_SESSION['errores_formulario'], $_SESSION['datos_formulario']);

if (!empty($devueltos)) {
    // CASO B: usamos lo que el usuario escribió
    $valores = [
        'nombre'     => $devueltos['nombre']     ?? '',
        'talla'      => $devueltos['talla']      ?? '',
        'color'      => $devueltos['color']      ?? '',
        'precio'     => $devueltos['precio']     ?? '',
        'stock'      => $devueltos['stock']      ?? '',
        'imagen_url' => $devueltos['imagen_url'] ?? '',
    ];
} else {
    // CASO A: usamos lo que hay en la base de datos
    //
    // El ?? '' es necesario porque color e imagen_url pueden venir
    // NULL de la base, y un NULL en un campo de formulario genera
    // advertencia en PHP 8.
    $valores = [
        'nombre'     => $prenda['nombre'],
        'talla'      => $prenda['talla'],
        'color'      => $prenda['color']      ?? '',
        'precio'     => $prenda['precio'],
        'stock'      => $prenda['stock'],
        'imagen_url' => $prenda['imagen_url'] ?? '',
    ];
}

$titulo = 'Editar prenda';
require_once 'includes/cabecera.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Editar prenda</h1>
                <p class="text-muted small mb-0">
                    Prenda #<?= (int) $prenda['id'] ?>
                </p>
            </div>
            <a href="consultar.php" class="btn btn-sm btn-outline-secondary">
                &larr; Volver al inventario
            </a>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <p class="fw-semibold mb-2">Revisa estos puntos antes de continuar:</p>
                <ul class="mb-0 small">
                    <?php foreach ($errores as $e): ?>
                        <li><?= limpiar($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <!-- action="actualizar.php" = a dónde van los datos.
                     Este es EL enlace entre los dos archivos. -->
                <form method="post" action="actualizar.php" id="formPrenda" novalidate>

                    <!-- ---------------------------------------------------
                         CAMPO OCULTO CON EL ID

                         type="hidden" significa que el usuario no lo ve
                         en pantalla, pero sí viaja con el formulario.

                         ¿POR QUÉ HACE FALTA? Porque al enviar el
                         formulario se hace una petición NUEVA al
                         servidor. PHP no recuerda nada de la petición
                         anterior: hay que volver a mandarle el id para
                         que actualizar.php sepa cuál fila cambiar.

                         ¡OJO! "Oculto" no significa "seguro": el usuario
                         puede cambiarlo con las herramientas del
                         navegador. Por eso actualizar.php lo vuelve a
                         validar cuando le llega.
                         --------------------------------------------------- -->
                    <input type="hidden" name="id" value="<?= (int) $prenda['id'] ?>">

                    <div class="row g-3">

                        <div class="col-12">
                            <label for="nombre" class="form-label">
                                Nombre de la prenda <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control"
                                   id="nombre" name="nombre"
                                   maxlength="60" required
                                   value="<?= limpiar($valores['nombre']) ?>">
                            <!-- Aquí se ve el "precargado": el atributo
                                 value trae el dato actual de la prenda. -->
                        </div>

                        <div class="col-md-4">
                            <label for="talla" class="form-label">
                                Talla <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="talla" name="talla" required>
                                <option value="">Selecciona...</option>
                                <?php
                                $tallas = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                                foreach ($tallas as $t):
                                    // La talla que ya tiene la prenda
                                    // queda preseleccionada al abrir.
                                    $sel = ($valores['talla'] === $t) ? 'selected' : '';
                                ?>
                                    <option value="<?= $t ?>" <?= $sel ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label for="color" class="form-label">Color</label>
                            <input type="text" class="form-control"
                                   id="color" name="color" maxlength="30"
                                   value="<?= limpiar($valores['color']) ?>">
                            <div class="form-text">Opcional</div>
                        </div>

                        <div class="col-md-6">
                            <label for="precio" class="form-label">
                                Precio <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control"
                                       id="precio" name="precio"
                                       min="0" step="0.01" required
                                       value="<?= limpiar($valores['precio']) ?>">
                            </div>
                            <div class="form-text">
                                Sin puntos de miles. Ejemplo: 45000
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="stock" class="form-label">
                                Stock <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control"
                                   id="stock" name="stock"
                                   min="0" step="1" required
                                   value="<?= limpiar($valores['stock']) ?>">
                            <div class="form-text">
                                Si llegó mercancía, SUMA las unidades nuevas a las que ya había.
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="imagen_url" class="form-label">
                                Dirección de la imagen
                            </label>
                            <input type="url" class="form-control"
                                   id="imagen_url" name="imagen_url"
                                   maxlength="255" placeholder="https://..."
                                   value="<?= limpiar($valores['imagen_url']) ?>">
                            <div class="form-text">Opcional</div>
                        </div>

                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-dark">
                            Guardar cambios
                        </button>
                        <a href="consultar.php" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/pie.php'; ?>
