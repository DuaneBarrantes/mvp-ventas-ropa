<?php
/**
 * =====================================================================
 *  ARCHIVO  : detalle.php
 *  PROYECTO : MVP Ventas de Ropa
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTA PÁGINA?
 *  Muestra TODA la información de UNA sola prenda.
 *
 *  ¿PARA QUÉ SIRVE SI YA ESTÁ EN LA LISTA?
 *  Porque en la lista caben pocas columnas: si pusiéramos la foto, la
 *  fecha de registro y el precio en dólares, la tabla no cabría en la
 *  pantalla. La página de detalle es donde se muestra todo con calma.
 *
 *  Es también la pantalla que usa la GUÍA 2: aquí se muestra el precio
 *  convertido a dólares usando la API REST.
 *
 *  ¿QUÉ RECIBE? El id por la dirección: detalle.php?id=3
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */

require_once 'includes/sesion.php';
exigir_sesion();
require_once 'includes/funciones.php';
require_once 'config/conexion.php';

// Cargamos el archivo de la API (contenido de la Guía 2)
require_once 'includes/api_dolar.php';


/* =====================================================================
 * 1. RECIBIR Y VALIDAR EL ID
 * ===================================================================== */
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: consultar.php?estado=no_existe');
    exit;
}


/* =====================================================================
 * 2. BUSCAR LA PRENDA
 * =====================================================================
 * Aquí sí traemos creado_en, que en la lista no mostrábamos.
 * ===================================================================== */
$consulta = $conexion->prepare(
    'SELECT id, nombre, talla, color, precio, stock, imagen_url, creado_en
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


/* =====================================================================
 * 3. CONSULTAR LA TASA DEL DÓLAR  (GUÍA 2 — API REST)
 * =====================================================================
 * Llamamos a la función que está en includes/api_dolar.php.
 *
 * FÍJATE EN ALGO IMPORTANTE: esta llamada va DESPUÉS de haber
 * consultado la base de datos, no antes.
 *
 * ¿Por qué el orden importa? Porque si la API está lenta o caída, ya
 * tenemos los datos de la prenda listos. Lo esencial (la prenda) no
 * depende de lo opcional (el dólar). Si fuera al revés, una API caída
 * dejaría al usuario sin poder ver su propia información.
 *
 * REGLA: lo que es tuyo va primero; lo que depende de terceros va
 * después y nunca debe ser indispensable.
 * ===================================================================== */
$tasa = obtener_tasa_dolar();


/* =====================================================================
 * 4. CALCULAR EL VALOR TOTAL DEL INVENTARIO DE ESTA PRENDA
 * =====================================================================
 * Regla de negocio sencilla: precio × unidades en stock.
 * Es una cuenta que hacemos en PHP, no en la base de datos, porque es
 * un cálculo de presentación.
 * ===================================================================== */
$valor_inventario = (float) $prenda['precio'] * (int) $prenda['stock'];

$titulo = 'Detalle de ' . $prenda['nombre'];
require_once 'includes/cabecera.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h1 class="h3 mb-1"><?= limpiar($prenda['nombre']) ?></h1>
                <p class="text-muted small mb-0">
                    Prenda #<?= (int) $prenda['id'] ?>
                    &middot; Registrada el
                    <?php
                    // date() da formato a una fecha, pero recibe un
                    // número de segundos, no un texto. strtotime()
                    // convierte el texto "2026-09-18 14:30:00" que
                    // viene de MySQL a ese número.
                    //
                    // 'd/m/Y' produce  18/09/2026
                    //   d = día con dos dígitos
                    //   m = mes con dos dígitos
                    //   Y = año con cuatro dígitos
                    echo date('d/m/Y', strtotime($prenda['creado_en']));
                    ?>
                </p>
            </div>

            <div class="d-flex gap-2">
                <a href="editar.php?id=<?= (int) $prenda['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                <a href="consultar.php" class="btn btn-outline-secondary btn-sm">&larr; Volver</a>
            </div>
        </div>

        <div class="row g-4">

            <!-- ============ COLUMNA IZQUIERDA: LA FOTO ============ -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-center p-3">

                        <?php if (!empty($prenda['imagen_url'])): ?>
                            <!-- Si la prenda tiene foto, la mostramos.

                                 El atributo alt es OBLIGATORIO: describe
                                 la imagen para las personas que usan
                                 lectores de pantalla, y es lo que se ve
                                 si la imagen no carga. -->
                            <img src="<?= limpiar($prenda['imagen_url']) ?>"
                                 alt="Fotografía de <?= limpiar($prenda['nombre']) ?>"
                                 class="img-fluid rounded"
                                 style="max-height:320px">
                            <!-- img-fluid hace que la imagen se achique
                                 si la pantalla es pequeña, en vez de
                                 salirse del recuadro. -->
                        <?php else: ?>
                            <!-- Si NO tiene foto, mostramos un espacio
                                 con un mensaje. Nunca se deja un hueco
                                 vacío sin explicación. -->
                            <div class="text-center text-muted py-5">
                                <div style="font-size:2.5rem">&#128444;</div>
                                <p class="small mb-0">Sin imagen registrada</p>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <!-- ============ COLUMNA DERECHA: LOS DATOS ============ -->
            <div class="col-md-7">

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h2 class="h6 text-muted mb-3">Información de la prenda</h2>

                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th class="text-muted" style="width:150px">Talla</th>
                                    <td><span class="badge text-bg-secondary"><?= limpiar($prenda['talla']) ?></span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Color</th>
                                    <td><?= limpiar($prenda['color']) ?: '<span class="text-muted">Sin color definido</span>' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Precio</th>
                                    <td class="fs-5 fw-semibold"><?= formato_pesos($prenda['precio']) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Stock</th>
                                    <td><?= etiqueta_stock((int) $prenda['stock']) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Valor en inventario</th>
                                    <td>
                                        <?= formato_pesos($valor_inventario) ?>
                                        <span class="text-muted small">
                                            (<?= formato_pesos($prenda['precio']) ?>
                                            &times; <?= (int) $prenda['stock'] ?>)
                                        </span>
                                        <!-- Mostramos la cuenta y no solo
                                             el resultado. Así el usuario
                                             entiende de dónde sale y puede
                                             verificarlo. -->
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- =====================================================
                     TARJETA DE LA API REST  (GUÍA 2)
                     ===================================================== -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">

                        <h2 class="h6 text-muted mb-3">
                            Precio en dólares
                            <span class="badge text-bg-info ms-1">Vía API REST</span>
                        </h2>

                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <span class="fs-4 fw-semibold">
                                <?= pesos_a_dolares((float) $prenda['precio'], (float) $tasa['valor']) ?>
                            </span>
                        </div>

                        <p class="small text-muted mb-2">
                            Tasa usada: 1 USD =
                            <?= formato_pesos($tasa['valor']) ?>
                        </p>

                        <?php
                        /* --------------------------------------------
                         * AVISO SEGÚN SI LA API RESPONDIÓ O NO.
                         *
                         * Aquí se ve por qué la función devuelve 'ok':
                         * el sistema siempre muestra un precio, pero es
                         * HONESTO sobre de dónde salió el dato.
                         *
                         * Mostrar un dato aproximado como si fuera
                         * exacto sería engañar al usuario.
                         * -------------------------------------------- */
                        ?>
                        <?php if ($tasa['ok']): ?>
                            <div class="alert alert-success py-2 small mb-0">
                                <?= limpiar($tasa['fuente']) ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning py-2 small mb-0">
                                <strong>Atención:</strong> <?= limpiar($tasa['fuente']) ?>.
                                <br>
                                <span class="text-muted">
                                    Motivo técnico: <?= limpiar($tasa['error']) ?>
                                </span>
                            </div>
                            <!-- Mostrar el motivo técnico sirve para
                                 aprender a diagnosticar. En un sistema
                                 publicado en internet, este detalle se
                                 guardaría en un archivo de registro y al
                                 usuario solo se le mostraría el aviso
                                 amarillo. -->
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>

        <!-- Zona de acciones peligrosas, separada del resto -->
        <div class="mt-4 pt-3 border-top">
            <a href="eliminar.php?id=<?= (int) $prenda['id'] ?>"
               class="btn btn-outline-danger btn-sm">
                Eliminar esta prenda
            </a>
        </div>

    </div>
</div>

<?php require_once 'includes/pie.php'; ?>
