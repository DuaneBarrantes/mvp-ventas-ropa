<?php
/**
 * =====================================================================
 *  ARCHIVO  : includes/sesion.php
 *  PROYECTO : MVP Ventas de Ropa
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTE ARCHIVO?
 *  Es el "portero" del sistema. Se encarga de dos cosas:
 *    1. Encender el sistema de sesiones de PHP.
 *    2. Revisar si quien está entrando ya inició sesión. Si NO,
 *       lo devuelve al login.
 *
 *  ¿QUÉ ES UNA SESIÓN?
 *  Es la memoria del servidor sobre un usuario mientras navega. Cuando
 *  alguien inicia sesión correctamente, guardamos su nombre en la
 *  sesión. Al pasar de una página a otra, el servidor "se acuerda" de
 *  él. Sin sesiones, habría que escribir la contraseña en CADA página.
 *
 *  ¿CÓMO SE USA?
 *  Se pone esta línea en la PRIMERA línea de toda página protegida:
 *      require_once 'includes/sesion.php';
 *
 *  ¡IMPORTANTE! Va antes de cualquier HTML. Si se imprime algo antes,
 *  PHP no puede redirigir y sale el error "headers already sent".
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */


/* ---------------------------------------------------------------------
 * 1. ENCENDER LA SESIÓN
 * ---------------------------------------------------------------------
 * session_status() nos dice en qué estado está la sesión.
 * PHP_SESSION_NONE significa "las sesiones funcionan, pero todavía no se
 * ha iniciado ninguna".
 *
 * ¿Por qué se pregunta primero en vez de llamar session_start() directo?
 * Porque si la sesión YA estaba iniciada, llamarla otra vez genera el
 * aviso "session already started". Preguntando primero, nunca falla.
 * --------------------------------------------------------------------- */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* ---------------------------------------------------------------------
 * 2. FUNCIÓN: ¿hay alguien con sesión iniciada?
 * ---------------------------------------------------------------------
 *  RECIBE  : nada
 *  DEVUELVE: true  (verdadero) si hay un usuario con sesión activa
 *            false (falso)     si no hay nadie
 *
 *  $_SESSION es un arreglo donde PHP guarda los datos de la sesión.
 *  isset() pregunta "¿existe esta casilla?". Si existe la casilla
 *  'usuario_id', es porque alguien inició sesión correctamente.
 * --------------------------------------------------------------------- */
function hay_sesion_activa(): bool
{
    return isset($_SESSION['usuario_id']);
}


/* ---------------------------------------------------------------------
 * 3. FUNCIÓN: exigir que haya sesión iniciada
 * ---------------------------------------------------------------------
 *  RECIBE  : nada
 *  DEVUELVE: nada — pero si NO hay sesión, corta la ejecución y manda
 *            al usuario al login.
 *
 *  Esta es la función que protege las páginas. Sin ella, cualquiera
 *  podría escribir la dirección de consultar.php en el navegador y entrar
 *  sin contraseña.
 * --------------------------------------------------------------------- */
function exigir_sesion(): void
{
    // Si NO hay sesión activa...  (el signo ! significa "no")
    if (!hay_sesion_activa()) {

        // header('Location: ...') le ordena al navegador ir a otra página.
        header('Location: login.php?estado=sin_sesion');

        // exit detiene el programa AQUÍ MISMO.
        //
        // ¿Por qué es obligatorio? Porque header() solo ENVÍA la orden,
        // pero PHP seguiría ejecutando el resto del archivo y alcanzaría
        // a mostrar los datos protegidos antes de que el navegador
        // cambie de página. Sin exit, la protección no sirve.
        exit;
    }
}


/* ---------------------------------------------------------------------
 * 4. FUNCIÓN: obtener el nombre del usuario conectado
 * ---------------------------------------------------------------------
 *  RECIBE  : nada
 *  DEVUELVE: el nombre de usuario (texto). Si no hay nadie, 'invitado'.
 *
 *  Se usa para mostrar "Hola, admin" en la barra superior.
 *
 *  El operador ?? se llama "coalescencia nula" y significa:
 *  "usa el valor de la izquierda; si no existe, usa el de la derecha".
 *  Evita el error "Undefined index" cuando la casilla no existe.
 * --------------------------------------------------------------------- */
function usuario_actual(): string
{
    return $_SESSION['usuario_nombre'] ?? 'invitado';
}


/* ---------------------------------------------------------------------
 * 5. FUNCIÓN: cerrar la sesión por completo
 * ---------------------------------------------------------------------
 *  RECIBE  : nada
 *  DEVUELVE: nada
 *
 *  Cerrar sesión bien requiere TRES pasos, no uno. Si solo se hace el
 *  primero, quedan rastros que permiten reabrir la sesión.
 * --------------------------------------------------------------------- */
function cerrar_sesion(): void
{
    // Paso 1: vaciar los datos guardados en la sesión.
    $_SESSION = [];

    // Paso 2: borrar la galleta (cookie) que identifica la sesión en el
    // navegador del usuario. Si no se borra, el navegador seguiría
    // mandando el mismo identificador de sesión.
    if (ini_get('session.use_cookies')) {
        // Recuperamos la configuración de la cookie de sesión
        $p = session_get_cookie_params();
        // La reescribimos con fecha de vencimiento en el PASADO
        // (time() - 42000 = hace unas 11 horas), lo que hace que el
        // navegador la borre inmediatamente.
        setcookie(
            session_name(),        // nombre de la cookie (normalmente PHPSESSID)
            '',                    // valor vacío
            time() - 42000,        // vencida
            $p['path'],
            $p['domain'],
            $p['secure'],
            $p['httponly']
        );
    }

    // Paso 3: destruir la sesión en el servidor.
    session_destroy();
}
