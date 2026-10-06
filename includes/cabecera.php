<?php
/**
 * =====================================================================
 *  ARCHIVO  : includes/cabecera.php
 *  PROYECTO : MVP Ventas de Ropa
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTE ARCHIVO?
 *  Contiene la parte de ARRIBA que se repite en todas las páginas: la
 *  etiqueta <html>, los enlaces a Bootstrap y el menú de navegación.
 *
 *  ¿POR QUÉ NO SE ESCRIBE EN CADA PÁGINA?
 *  Porque si el menú está copiado en 8 archivos y mañana hay que
 *  agregarle una opción, tocaría editar los 8 y seguro se olvida alguno.
 *  Así se edita UNA vez y cambia en todo el sistema.
 *
 *  ¿CÓMO SE USA?
 *  Antes de esta línea, la página define la variable $titulo:
 *      $titulo = 'Inventario';
 *      require_once 'includes/cabecera.php';
 *
 *  ¿QUÉ RECIBE?  la variable $titulo (opcional)
 *  ¿QUÉ ENTREGA? el HTML de apertura de la página
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */

// Si la página que nos llamó no definió un título, usamos uno por
// defecto. El operador ?? evita el error "Undefined variable".
$titulo = $titulo ?? 'MVP Ventas de Ropa';

// basename(...) saca solo el nombre del archivo actual.
// Ejemplo: de "/mvp-ropa/registrar.php" devuelve "registrar.php".
// Lo usamos abajo para marcar en el menú en qué página estamos.
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<!-- lang="es" le avisa al navegador que el contenido está en español.
     Sirve para el corrector ortográfico y para los lectores de pantalla
     de personas con discapacidad visual. -->
<html lang="es">
<head>
    <!-- charset utf-8: para que las tildes y la ñ se vean bien.
         Debe ir de PRIMERO dentro del <head>. -->
    <meta charset="UTF-8">

    <!-- viewport: hace que la página se adapte al tamaño del celular.
         Sin esta línea, en el celular se vería la página de computador
         encogida y con letra minúscula. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- El título que aparece en la pestaña del navegador -->
    <title><?= limpiar($titulo) ?> · MVP Ventas de Ropa</title>

    <!-- BOOTSTRAP 5.3.3 desde CDN.
         CDN significa "red de entrega de contenido": el archivo no está
         en nuestro computador, se descarga de internet.

         VENTAJA : no hay que descargar ni guardar nada.
         DESVENTAJA: si no hay internet, la página se ve sin estilos.

         Para la sustentación: si el salón no tiene internet, hay que
         descargar bootstrap.min.css y guardarlo en assets/css/. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Nuestra hoja de estilos propia.
         Va DESPUÉS de Bootstrap a propósito: en CSS, cuando dos reglas
         chocan, gana la que se cargó de último. Así podemos modificar
         los estilos de Bootstrap sin pelear con él. -->
    <link href="assets/css/estilos.css" rel="stylesheet">
</head>
<body>

<!-- =================================================================
     BARRA DE NAVEGACIÓN
     navbar-expand-lg = en pantallas grandes se ve extendida; en celular
                        se colapsa en el botón de las tres rayitas.
     ================================================================= -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">

        <!-- Logo / nombre del sistema. Al hacer clic lleva al inicio. -->
        <a class="navbar-brand fw-bold" href="consultar.php">
            Ventas de Ropa
        </a>

        <!-- Botón de las tres rayitas, solo visible en celular.
             data-bs-toggle y data-bs-target son instrucciones de
             Bootstrap: "al hacer clic, muestra u oculta el elemento
             que tiene el id menuPrincipal". -->
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menuPrincipal"
                aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav me-auto">

                <!-- Cada opción del menú.
                     El PHP de la clase revisa si estamos en esa página
                     para pintarla resaltada. Es el detalle que hace que
                     el usuario sepa dónde está parado. -->
                <li class="nav-item">
                    <a class="nav-link <?= $pagina_actual === 'consultar.php' ? 'active fw-semibold' : '' ?>"
                       href="consultar.php">Inventario</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $pagina_actual === 'registrar.php' ? 'active fw-semibold' : '' ?>"
                       href="registrar.php">Registrar prenda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $pagina_actual === 'estadisticas.php' ? 'active fw-semibold' : '' ?>"
                       href="estadisticas.php">Estadísticas</a>
                </li>
            </ul>

            <!-- Lado derecho: quién está conectado y el botón de salir -->
            <div class="d-flex align-items-center gap-3">
                <span class="text-white-50 small">
                    Hola, <strong class="text-white"><?= limpiar(usuario_actual()) ?></strong>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    Cerrar sesión
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- container = caja centrada con márgenes automáticos.
     Se ABRE aquí y se CIERRA en pie.php. Por eso las dos partes
     siempre van juntas: si se incluye la cabecera, hay que incluir
     el pie, o el HTML queda sin cerrar. -->
<main class="container pb-5">

    <!-- Aquí se imprime el aviso de color (creado, editado, eliminado).
         $_GET trae lo que viene en la dirección después del signo ?
         Ejemplo: consultar.php?estado=creado  →  $_GET['estado'] = 'creado' -->
    <?= mensaje_estado($_GET['estado'] ?? null) ?>
