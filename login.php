<?php
/**
 * =====================================================================
 *  ARCHIVO  : login.php
 *  PROYECTO : MVP Ventas de Ropa
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTA PÁGINA?
 *  Es la puerta de entrada al sistema. Muestra el formulario de usuario
 *  y contraseña, y verifica si los datos son correctos.
 *
 *  ¿CÓMO FUNCIONA UNA PÁGINA QUE RECIBE UN FORMULARIO?
 *  El mismo archivo se usa para DOS cosas distintas:
 *    1. Cuando se entra por primera vez  → muestra el formulario vacío
 *    2. Cuando se pulsa "Entrar"         → recibe los datos y los revisa
 *
 *  Se distingue con $_SERVER['REQUEST_METHOD']:
 *     'GET'  = el usuario solo está viendo la página
 *     'POST' = el usuario envió el formulario
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */

// -----------------------------------------------------------------
// Cargamos las herramientas que necesita esta página.
//
// require_once significa:
//   require = "es obligatorio; si no existe, detén todo"
//   _once   = "cárgalo una sola vez, aunque te lo pidan dos veces"
//
// OJO: aquí usamos sesion.php pero NO llamamos exigir_sesion(),
// porque esta es justamente la página donde el usuario TODAVÍA no ha
// iniciado sesión. Sería una trampa infinita.
// -----------------------------------------------------------------
require_once 'includes/sesion.php';
require_once 'includes/funciones.php';
require_once 'config/conexion.php';


// -----------------------------------------------------------------
// Si el usuario YA tiene sesión iniciada y vuelve a entrar al login,
// no tiene sentido mostrarle el formulario: lo mandamos al inventario.
// -----------------------------------------------------------------
if (hay_sesion_activa()) {
    header('Location: consultar.php');
    exit;
}


// Variable donde guardaremos el mensaje de error, si lo hay.
$error = '';


/* =====================================================================
 * PROCESAR EL FORMULARIO — solo si llegó por POST
 * ===================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---- Paso 1: recoger lo que escribió el usuario ----
    // $_POST es el arreglo con los datos del formulario.
    // La casilla se llama igual que el atributo name="" del input.
    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = $_POST['clave'] ?? '';

    // OJO: al usuario sí le aplicamos trim() (quitar espacios), pero a
    // la CONTRASEÑA NO. ¿Por qué? Porque un espacio puede ser parte
    // legítima de la contraseña, y quitárselo haría que nunca entre.

    // ---- Paso 2: revisar que no vengan vacíos ----
    if ($usuario === '' || $clave === '') {
        $error = 'Debes escribir el usuario y la contraseña.';

    } else {
        // ---- Paso 3: buscar ese usuario en la base de datos ----
        //
        // ¡ATENCIÓN! Aquí está la parte más importante de seguridad de
        // todo el proyecto: la CONSULTA PREPARADA.
        //
        // ¿QUÉ ES UNA CONSULTA PREPARADA?
        // Es separar la ORDEN de los DATOS. Primero se le manda a MySQL
        // la estructura de la consulta con signos de interrogación (?)
        // en los huecos, y DESPUÉS se le mandan los valores aparte.
        //
        // ¿POR QUÉ NO SE ESCRIBE PEGANDO EL TEXTO?
        // Si escribiéramos:
        //     "SELECT * FROM usuarios WHERE usuario = '$usuario'"
        // alguien podría escribir en el campo usuario esto:
        //     ' OR '1'='1
        // y la consulta se convertiría en:
        //     SELECT * FROM usuarios WHERE usuario = '' OR '1'='1'
        // Como '1'='1' siempre es verdad, ¡entraría sin contraseña!
        //
        // Eso se llama INYECCIÓN SQL, y es el ataque más común contra
        // sistemas web. Con la consulta preparada es imposible, porque
        // MySQL ya sabe cuál es la orden antes de ver los datos, y
        // trata lo que llega como texto, nunca como instrucción.

        // 3.1 — Preparar: mandamos la orden con un hueco (?)
        $consulta = $conexion->prepare(
            'SELECT id, usuario, clave, rol FROM usuarios WHERE usuario = ? LIMIT 1'
        );
        // LIMIT 1 = "tráeme máximo un resultado". Como el usuario es
        // UNIQUE, no puede haber dos, pero se lo decimos igual a MySQL
        // para que deje de buscar apenas lo encuentre.

        // 3.2 — Amarrar el valor al hueco
        //
        // El primer parámetro "s" indica el TIPO del dato:
        //     s = string  (texto)
        //     i = integer (número entero)
        //     d = double  (número con decimales)
        //     b = blob    (archivo binario)
        //
        // Hay una letra por cada signo ?. Aquí hay un solo ?, entonces
        // una sola letra: "s", porque el nombre de usuario es texto.
        $consulta->bind_param('s', $usuario);

        // 3.3 — Ejecutar la consulta en el servidor de base de datos
        $consulta->execute();

        // 3.4 — Recoger el resultado
        $resultado = $consulta->get_result();

        // fetch_assoc() saca UNA fila del resultado como arreglo:
        //     $fila['usuario'], $fila['clave'], etc.
        // Si no encontró nada, devuelve null.
        $fila = $resultado->fetch_assoc();

        // 3.5 — Cerrar la consulta y liberar la memoria que usaba
        $consulta->close();

        // ---- Paso 4: verificar la contraseña ----
        //
        // password_verify() compara la contraseña que escribió el
        // usuario contra el hash guardado en la base de datos.
        //
        // ¿POR QUÉ NO SE COMPARA CON == DIRECTAMENTE?
        // Porque en la base NO está la contraseña: está su hash, que es
        // un texto larguísimo y distinto. Y el hash no se puede
        // devolver al original — es de una sola vía.
        //
        // Lo que hace password_verify es volver a calcular el hash de
        // lo que escribió el usuario (usando la misma "sal" que quedó
        // guardada dentro del hash) y comparar los resultados.

        if ($fila !== null && password_verify($clave, $fila['clave'])) {

            // ¡Contraseña correcta! Iniciamos la sesión.

            // -----------------------------------------------------
            // session_regenerate_id(true) cambia el identificador de
            // la sesión al momento de entrar.
            //
            // ¿PARA QUÉ? Protege contra un ataque llamado "fijación de
            // sesión", donde un atacante le pasa a la víctima un
            // identificador que él ya conoce, y luego lo reutiliza.
            // Al cambiarlo justo al iniciar sesión, el identificador
            // viejo queda inservible.
            // -----------------------------------------------------
            session_regenerate_id(true);

            // Guardamos en la sesión quién entró.
            // (int) convierte el texto a número entero, porque todo lo
            // que sale de MySQL en PHP llega como texto.
            $_SESSION['usuario_id']     = (int) $fila['id'];
            $_SESSION['usuario_nombre'] = $fila['usuario'];
            $_SESSION['usuario_rol']    = $fila['rol'];

            // Lo mandamos al inventario. Fin del login.
            header('Location: consultar.php');
            exit;

        } else {
            // Contraseña incorrecta O el usuario no existe.
            //
            // ¡FÍJATE EN EL MENSAJE! Decimos "usuario o contraseña
            // incorrectos", sin aclarar cuál de los dos falló.
            //
            // ¿POR QUÉ? Porque si dijéramos "ese usuario no existe",
            // un atacante podría ir probando nombres hasta descubrir
            // cuáles sí existen, y después concentrarse en adivinarles
            // la contraseña. Eso se llama "enumeración de usuarios".
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}

// Título que usará la cabecera
$titulo = 'Iniciar sesión';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · MVP Ventas de Ropa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/estilos.css" rel="stylesheet">
</head>
<!-- Esta página NO usa cabecera.php a propósito: no debe mostrar el
     menú de navegación, porque el usuario todavía no ha entrado. -->
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center">
        <!-- col-md-5: en pantallas medianas y grandes ocupa 5 de 12
             columnas (centrado). En celular ocupa todo el ancho. -->
        <div class="col-md-5 col-lg-4">

            <div class="card shadow-sm mt-5">
                <div class="card-body p-4">

                    <h1 class="h4 text-center mb-1">MVP Ventas de Ropa</h1>
                    <p class="text-center text-muted small mb-4">
                        Ingresa con tu usuario para administrar el inventario
                    </p>

                    <!-- Aviso que llega por la dirección, por ejemplo
                         cuando el portero nos devolvió aquí:
                         login.php?estado=sin_sesion -->
                    <?= mensaje_estado($_GET['estado'] ?? null) ?>

                    <!-- Aviso de error del intento de login.
                         Solo se dibuja si $error tiene contenido. -->
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger py-2 small">
                            <?= limpiar($error) ?>
                        </div>
                    <?php endif; ?>

                    <!-- FORMULARIO
                         method="post" = los datos viajan ocultos en el
                         cuerpo de la petición, no en la dirección.
                         Con method="get" la contraseña saldría escrita
                         en la barra del navegador: jamás se hace eso.

                         action="" (vacío) = envía al MISMO archivo. -->
                    <form method="post" action="" novalidate>

                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <!-- name="usuario" es la llave con la que
                                 PHP lo recoge: $_POST['usuario']
                                 autofocus = el cursor arranca aquí -->
                            <input type="text"
                                   class="form-control"
                                   id="usuario"
                                   name="usuario"
                                   required
                                   autofocus
                                   value="<?= limpiar($_POST['usuario'] ?? '') ?>">
                            <!-- El value devuelve lo que ya había escrito
                                 para que no tenga que repetirlo si se
                                 equivocó solo en la contraseña. -->
                        </div>

                        <div class="mb-4">
                            <label for="clave" class="form-label">Contraseña</label>
                            <!-- type="password" hace que se vean puntos
                                 en vez de las letras.
                                 NUNCA se devuelve el value de una
                                 contraseña, por seguridad. -->
                            <input type="password"
                                   class="form-control"
                                   id="clave"
                                   name="clave"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-dark w-100">
                            Entrar
                        </button>
                    </form>

                    <!-- Recordatorio del usuario de práctica.
                         En un sistema real esto NO se muestra jamás. -->
                    <div class="alert alert-secondary small mt-4 mb-0">
                        <strong>Usuario de práctica:</strong><br>
                        Usuario: <code>admin</code> &nbsp;·&nbsp;
                        Contraseña: <code>admin123</code>
                    </div>

                </div>
            </div>

            <p class="text-center text-muted small mt-3">
                SENA · CIAS Regional Boyacá · Ficha 3168431
            </p>
        </div>
    </div>
</div>

</body>
</html>
