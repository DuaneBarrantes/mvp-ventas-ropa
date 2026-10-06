<?php
/**
 * =====================================================================
 *  ARCHIVO  : registrar.php
 *  PROYECTO : MVP Ventas de Ropa
 *  OPERACIÓN: CREATE (crear) — parte 1 de 2
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTE ARCHIVO?
 *  UNA sola cosa: mostrar el formulario para registrar una prenda.
 *
 *  Este archivo NO guarda nada en la base de datos. Solo dibuja los
 *  campos en pantalla. Cuando el usuario pulsa "Guardar prenda", los
 *  datos se van a guardar.php, que es el que sí escribe en la base.
 *
 *  ¿POR QUÉ SEPARADO EN DOS ARCHIVOS?
 *  Es la misma regla que se aplicó a las carpetas: cada archivo hace
 *  UNA sola cosa. Así:
 *     - Si el formulario se ve mal  → el problema está en registrar.php
 *     - Si el dato no se guarda     → el problema está en guardar.php
 *  No hay que leer 200 líneas para encontrar dónde falló.
 *
 *  ¿QUÉ RECIBE? Nada obligatorio. Si guardar.php lo devolvió por un
 *               error de validación, recibe los errores y los datos
 *               que el usuario ya había escrito (ver más abajo).
 *  ¿QUÉ ENTREGA? El formulario en pantalla.
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */

require_once 'includes/sesion.php';

// El portero: si no ha iniciado sesión, lo devuelve al login
exigir_sesion();

require_once 'includes/funciones.php';


/* =====================================================================
 * RECUPERAR LOS ERRORES Y DATOS QUE DEJÓ guardar.php
 * =====================================================================
 * Aquí hay un detalle importante que vale la pena entender.
 *
 * EL PROBLEMA:
 * Cuando guardar.php encuentra un error (por ejemplo, precio negativo),
 * tiene que devolver al usuario a este formulario. Pero al redirigir,
 * PHP arranca de cero: no se acuerda de nada de la petición anterior.
 * Sin más, el usuario volvería a un formulario en blanco y tendría que
 * escribirlo TODO otra vez. Eso enfurece a cualquiera.
 *
 * LA SOLUCIÓN:
 * Antes de redirigir, guardar.php deja los errores y lo escrito en
 * $_SESSION, que es la memoria del servidor sobre ese usuario. Aquí lo
 * recogemos y lo borramos enseguida.
 *
 * ¿POR QUÉ SE BORRA APENAS SE LEE?
 * Porque si no, los mensajes de error quedarían pegados y volverían a
 * aparecer la próxima vez que el usuario entre al formulario, aunque
 * ya no haya ningún error. Se usa una vez y se bota.
 *
 * A esta técnica se le dice "mensaje de un solo uso". Piénsalo como
 * una nota pegada en la puerta: el que llega la lee y la arranca.
 * ===================================================================== */

// ?? [] significa: "si no existe, usa un arreglo vacío"
$errores = $_SESSION['errores_formulario'] ?? [];
$valores = $_SESSION['datos_formulario']   ?? [];

// unset() borra esas casillas de la sesión. Leídas y botadas.
unset($_SESSION['errores_formulario'], $_SESSION['datos_formulario']);


/* ---------------------------------------------------------------------
 * VALORES POR DEFECTO DEL FORMULARIO
 * ---------------------------------------------------------------------
 * Si el usuario entra por primera vez, todos los campos van vacíos.
 * Si volvió por un error, se rellenan con lo que ya había escrito.
 *
 * El operador ?? revisa campo por campo: "si en $valores viene este
 * campo úsalo; si no, déjalo vacío".
 * --------------------------------------------------------------------- */
$valores = [
    'nombre'     => $valores['nombre']     ?? '',
    'talla'      => $valores['talla']      ?? '',
    'color'      => $valores['color']      ?? '',
    'precio'     => $valores['precio']     ?? '',
    'stock'      => $valores['stock']      ?? '',
    'imagen_url' => $valores['imagen_url'] ?? '',
];

$titulo = 'Registrar prenda';
require_once 'includes/cabecera.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Registrar prenda</h1>
            <a href="consultar.php" class="btn btn-sm btn-outline-secondary">
                &larr; Volver al inventario
            </a>
        </div>

        <!-- -------------------------------------------------------
             LISTA DE ERRORES
             Solo se dibuja si guardar.php nos devolvió errores.
             ------------------------------------------------------- -->
        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <p class="fw-semibold mb-2">
                    Revisa estos puntos antes de continuar:
                </p>
                <ul class="mb-0 small">
                    <?php foreach ($errores as $e): ?>
                        <li><?= limpiar($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <!-- ---------------------------------------------------
                     EL FORMULARIO

                     action="guardar.php" = a dónde se mandan los datos
                     cuando el usuario pulsa el botón. Este es EL enlace
                     entre los dos archivos.

                     method="post" = los datos viajan ocultos en el
                     cuerpo de la petición, no en la barra de direcciones.

                     id="formPrenda" lo usa validaciones.js para
                     revisarlo antes de enviarlo.

                     novalidate desactiva los mensajes automáticos del
                     navegador para que se vean los nuestros, que están
                     en español.
                     --------------------------------------------------- -->
                <form method="post" action="guardar.php" id="formPrenda" novalidate>

                    <div class="row g-3">

                        <!-- ---------- NOMBRE ---------- -->
                        <div class="col-12">
                            <label for="nombre" class="form-label">
                                Nombre de la prenda <span class="text-danger">*</span>
                            </label>
                            <!-- name="nombre" es la llave con la que
                                 guardar.php lo recoge: $_POST['nombre']
                                 Si el name no coincide, el dato no llega. -->
                            <input type="text"
                                   class="form-control"
                                   id="nombre"
                                   name="nombre"
                                   maxlength="60"
                                   required
                                   value="<?= limpiar($valores['nombre']) ?>">
                            <!-- maxlength="60" coincide con el VARCHAR(60)
                                 de la tabla. Los límites del formulario
                                 SIEMPRE deben coincidir con los de la
                                 base de datos. -->
                            <div class="form-text">
                                Ejemplo: Camiseta básica, Jean clásico
                            </div>
                        </div>

                        <!-- ---------- TALLA ---------- -->
                        <div class="col-md-4">
                            <label for="talla" class="form-label">
                                Talla <span class="text-danger">*</span>
                            </label>
                            <!-- Un <select> y no un campo de texto,
                                 porque las tallas son una lista cerrada.
                                 Así nadie escribe "mediana" o "Md" y
                                 daña los datos. -->
                            <select class="form-select" id="talla" name="talla" required>
                                <option value="">Selecciona...</option>
                                <?php
                                // En vez de escribir 6 líneas <option>
                                // casi iguales, las generamos con un
                                // bucle. Si mañana se agrega una talla,
                                // se agrega a esta lista y ya.
                                $tallas = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                                foreach ($tallas as $t):
                                    // Si esta es la talla que el usuario
                                    // ya había elegido, queda marcada.
                                    $sel = ($valores['talla'] === $t) ? 'selected' : '';
                                ?>
                                    <option value="<?= $t ?>" <?= $sel ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- ---------- COLOR ---------- -->
                        <div class="col-md-8">
                            <label for="color" class="form-label">Color</label>
                            <input type="text"
                                   class="form-control"
                                   id="color"
                                   name="color"
                                   maxlength="30"
                                   value="<?= limpiar($valores['color']) ?>">
                            <!-- Sin "required" porque la columna acepta
                                 NULL: hay prendas estampadas que no
                                 tienen un color único. -->
                            <div class="form-text">Opcional</div>
                        </div>

                        <!-- ---------- PRECIO ---------- -->
                        <div class="col-md-6">
                            <label for="precio" class="form-label">
                                Precio <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number"
                                       class="form-control"
                                       id="precio"
                                       name="precio"
                                       min="0"
                                       step="0.01"
                                       required
                                       value="<?= limpiar($valores['precio']) ?>">
                                <!-- step="0.01" permite decimales de a
                                     centavo. min="0" impide negativos. -->
                            </div>
                            <div class="form-text">
                                Sin puntos de miles. Ejemplo: 45000
                            </div>
                        </div>

                        <!-- ---------- STOCK ---------- -->
                        <div class="col-md-6">
                            <label for="stock" class="form-label">
                                Stock <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="stock"
                                   name="stock"
                                   min="0"
                                   step="1"
                                   required
                                   value="<?= limpiar($valores['stock']) ?>">
                            <!-- step="1" obliga a enteros: no existe
                                 media prenda en inventario. -->
                            <div class="form-text">Unidades disponibles</div>
                        </div>

                        <!-- ---------- IMAGEN ---------- -->
                        <div class="col-12">
                            <label for="imagen_url" class="form-label">
                                Dirección de la imagen
                            </label>
                            <input type="url"
                                   class="form-control"
                                   id="imagen_url"
                                   name="imagen_url"
                                   maxlength="255"
                                   placeholder="https://..."
                                   value="<?= limpiar($valores['imagen_url']) ?>">
                            <div class="form-text">
                                Opcional. Pega la dirección de una foto que esté en internet.
                                <br>
                                <em>Reto del equipo: cambiar esto por una subida de archivo real.</em>
                            </div>
                        </div>

                    </div>

                    <!-- ---------- BOTONES ---------- -->
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-dark">
                            Guardar prenda
                        </button>
                        <a href="consultar.php" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>

                    <p class="text-muted small mt-3 mb-0">
                        Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                    </p>

                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/pie.php'; ?>
