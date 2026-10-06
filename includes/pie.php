<?php
/**
 * =====================================================================
 *  ARCHIVO  : includes/pie.php
 *  PROYECTO : MVP Ventas de Ropa
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTE ARCHIVO?
 *  Contiene la parte de ABAJO que se repite en todas las páginas: el
 *  cierre del contenido, el pie de página y los archivos JavaScript.
 *
 *  Es la pareja de cabecera.php. Siempre van juntos:
 *      cabecera.php  ABRE  <html>, <body> y <main>
 *      pie.php       CIERRA </main>, </body> y </html>
 *
 *  ¿CÓMO SE USA? Al final de cada página:
 *      require_once 'includes/pie.php';
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */
?>

</main><!-- cierra el <main class="container"> que abrió cabecera.php -->

<!-- =================================================================
     PIE DE PÁGINA
     mt-auto empuja el pie hacia abajo; py-4 le da aire arriba y abajo.
     ================================================================= -->
<footer class="bg-dark text-white-50 py-4 mt-5">
    <div class="container text-center small">
        <div class="mb-1">
            <strong class="text-white">MVP Ventas de Ropa</strong>
            &middot; Proyecto formativo SENA
        </div>
        <div>
            CIAS Regional Boyacá &middot; Programación de Software (233104)
            &middot; Ficha 3168431
        </div>
        <div>
            RAP 03 — Codificar el software &middot; Competencia 220501096
        </div>
        <div class="mt-2">
            <!-- date('Y') pone el año actual automáticamente.
                 Así el pie no queda desactualizado el 1 de enero. -->
            &copy; <?= date('Y') ?> &middot; Instructor: Manuel Alejandro Ordóñez Hernández
        </div>
    </div>
</footer>

<!-- =================================================================
     ARCHIVOS JAVASCRIPT
     ¿POR QUÉ VAN AL FINAL Y NO EN EL <head>?

     Porque el navegador lee la página de arriba hacia abajo. Si el
     JavaScript va arriba, el navegador se detiene a descargarlo antes
     de dibujar el contenido, y el usuario ve una pantalla en blanco
     más tiempo.

     Además, nuestro JavaScript necesita que los elementos YA EXISTAN
     para poder manipularlos. Si corriera antes de que el formulario se
     dibuje, no lo encontraría.
     ================================================================= -->

<!-- El "bundle" de Bootstrap incluye Popper, necesario para que
     funcionen el menú desplegable y los avisos que se cierran. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Nuestro JavaScript propio: valida el formulario antes de enviarlo -->
<script src="assets/js/validaciones.js"></script>

</body>
</html>
