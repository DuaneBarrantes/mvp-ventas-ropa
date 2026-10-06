/* =====================================================================
   ARCHIVO  : assets/js/validaciones.js
   PROYECTO : MVP Ventas de Ropa
   ---------------------------------------------------------------------
   ¿QUÉ HACE ESTE ARCHIVO?
   Revisa el formulario ANTES de enviarlo al servidor, y le avisa al
   usuario si algo está mal, sin tener que recargar la página.

   ¡LA PREGUNTA MÁS IMPORTANTE DE LA SUSTENTACIÓN!
   "Si PHP ya valida, ¿para qué validar también en JavaScript?"

   Respuesta: porque hacen cosas DISTINTAS.

     JavaScript valida para la COMODIDAD del usuario:
       - Avisa al instante, sin recargar
       - No le hace perder lo que ya escribió
       - Marca exactamente cuál campo está mal

     PHP valida para la SEGURIDAD del sistema:
       - JavaScript corre en el computador del USUARIO, y el usuario
         lo puede desactivar, modificar o saltárselo completamente
       - Alguien puede mandar datos sin pasar por nuestro formulario
       - PHP corre en el SERVIDOR, donde el usuario no puede tocarlo

   La frase para memorizar:
   "La validación del cliente es por comodidad; la del servidor es por
   seguridad. Si tuviera que quedarme con una sola, me quedo con la del
   servidor."

   Y el error que NO hay que cometer: quitar la validación de PHP
   porque "ya la hace JavaScript". Eso deja el sistema desprotegido.
   ---------------------------------------------------------------------
   SENA · CIAS Regional Boyacá · Programación de Software (233104)
   Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
   ===================================================================== */


/* ---------------------------------------------------------------------
   ESPERAR A QUE LA PÁGINA ESTÉ LISTA
   ---------------------------------------------------------------------
   'DOMContentLoaded' es un evento que el navegador dispara cuando ya
   terminó de leer todo el HTML.

   ¿POR QUÉ ESPERAR?
   Porque si el JavaScript corre antes de que el formulario exista en
   la página, no lo va a encontrar y no hará nada. Sería como buscar un
   libro en un estante que todavía no han armado.
   --------------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', function () {

    // ----------------------------------------------------------------
    // Buscar el formulario por su id.
    // En crear.php y editar.php el formulario tiene id="formPrenda".
    // ----------------------------------------------------------------
    const formulario = document.getElementById('formPrenda');

    // Si no lo encuentra, devuelve null.
    //
    // Esta revisión es IMPORTANTE: este mismo archivo se carga en TODAS
    // las páginas (está en el pie), pero el formulario solo existe en
    // crear.php y editar.php. Sin este "if", en index.php el navegador
    // daría error en la consola.
    //
    // return sale de la función sin hacer nada más.
    if (!formulario) {
        return;
    }


    /* -----------------------------------------------------------------
       FUNCIÓN: marcarError
       -----------------------------------------------------------------
       RECIBE  : campo   → el elemento del formulario
                 mensaje → qué decirle al usuario
       DEVUELVE: nada (pinta el campo de rojo y muestra el mensaje)
       ----------------------------------------------------------------- */
    function marcarError(campo, mensaje) {
        // classList.add agrega una clase CSS al elemento.
        // La clase 'campo-error' está definida en estilos.css y le
        // pone el borde rojo.
        campo.classList.add('campo-error');

        // Buscamos si ya existe un mensaje de error debajo de este
        // campo, para no poner dos mensajes repetidos si el usuario
        // intenta enviar el formulario varias veces.
        //
        // nextElementSibling = "el elemento que está justo después"
        let aviso = campo.nextElementSibling;

        // Si no hay aviso, o el que hay no es nuestro, creamos uno.
        if (!aviso || !aviso.classList.contains('mensaje-error-js')) {
            // createElement crea un elemento nuevo en memoria
            aviso = document.createElement('div');
            // Le ponemos las clases: las de Bootstrap para el estilo,
            // y la nuestra para poder reconocerlo después.
            aviso.className = 'mensaje-error-js text-danger small mt-1';
            // insertAdjacentElement('afterend', ...) lo coloca justo
            // DESPUÉS del campo
            campo.insertAdjacentElement('afterend', aviso);
        }

        // textContent escribe el texto.
        //
        // ¡IMPORTANTE! Se usa textContent y NO innerHTML.
        // innerHTML interpretaría etiquetas HTML, lo que abre la puerta
        // a ataques XSS. textContent escribe texto plano y punto.
        // Es la misma idea que htmlspecialchars() en PHP, pero del
        // lado del navegador.
        aviso.textContent = mensaje;
    }


    /* -----------------------------------------------------------------
       FUNCIÓN: limpiarError
       -----------------------------------------------------------------
       Quita la marca roja y el mensaje de un campo.
       Se usa al empezar cada revisión, para borrar los errores de la
       vez anterior. Si no se limpiaran, quedarían mensajes viejos de
       campos que el usuario ya corrigió.
       ----------------------------------------------------------------- */
    function limpiarError(campo) {
        campo.classList.remove('campo-error');

        const aviso = campo.nextElementSibling;
        if (aviso && aviso.classList.contains('mensaje-error-js')) {
            // remove() borra el elemento de la página
            aviso.remove();
        }
    }


    /* -----------------------------------------------------------------
       EL EVENTO 'submit'
       -----------------------------------------------------------------
       Se dispara cuando el usuario pulsa el botón de enviar.

       El parámetro "evento" trae información de lo que está pasando y,
       lo más importante, permite CANCELARLO.
       ----------------------------------------------------------------- */
    formulario.addEventListener('submit', function (evento) {

        // Bandera: arrancamos suponiendo que todo está bien.
        // Si encontramos algún error, la cambiamos a false.
        let todoBien = true;

        // Recogemos los campos que vamos a revisar
        const nombre = document.getElementById('nombre');
        const talla  = document.getElementById('talla');
        const precio = document.getElementById('precio');
        const stock  = document.getElementById('stock');

        // Borramos los errores de intentos anteriores
        [nombre, talla, precio, stock].forEach(limpiarError);


        // ---- REVISAR NOMBRE ----
        // .value trae lo que el usuario escribió
        // .trim() quita los espacios de los extremos
        if (nombre.value.trim() === '') {
            marcarError(nombre, 'Escribe el nombre de la prenda.');
            todoBien = false;
        } else if (nombre.value.trim().length > 60) {
            // .length cuenta los caracteres
            marcarError(nombre, 'El nombre no puede pasar de 60 caracteres.');
            todoBien = false;
        }


        // ---- REVISAR TALLA ----
        if (talla.value === '') {
            marcarError(talla, 'Selecciona una talla.');
            todoBien = false;
        }


        // ---- REVISAR PRECIO ----
        if (precio.value.trim() === '') {
            marcarError(precio, 'Escribe el precio.');
            todoBien = false;
        } else if (isNaN(precio.value)) {
            // isNaN significa "is Not a Number" (no es un número).
            // Devuelve true cuando el valor NO se puede leer como
            // número, por ejemplo si escriben "45.000" con punto de
            // miles o "abc".
            marcarError(precio, 'El precio debe ser un número, sin puntos de miles.');
            todoBien = false;
        } else if (parseFloat(precio.value) < 0) {
            // parseFloat convierte el texto a número con decimales.
            // Hace falta porque .value SIEMPRE devuelve texto, aunque
            // el campo sea type="number".
            //
            // Sin parseFloat, comparar "-5" < 0 podría dar resultados
            // raros, porque estaríamos comparando texto con número.
            marcarError(precio, 'El precio no puede ser negativo.');
            todoBien = false;
        }


        // ---- REVISAR STOCK ----
        if (stock.value.trim() === '') {
            marcarError(stock, 'Escribe el stock.');
            todoBien = false;
        } else if (!Number.isInteger(Number(stock.value))) {
            // Number() convierte el texto a número.
            // Number.isInteger() revisa que NO tenga decimales.
            // Así rechazamos "3.5": no existe media prenda.
            marcarError(stock, 'El stock debe ser un número entero, sin decimales.');
            todoBien = false;
        } else if (parseInt(stock.value, 10) < 0) {
            // parseInt convierte a entero.
            // El 10 significa "en sistema decimal" (base 10). Ponerlo
            // es una buena costumbre: sin él, en navegadores viejos un
            // texto que empieza con 0 se interpretaba en base 8.
            marcarError(stock, 'El stock no puede ser negativo.');
            todoBien = false;
        }


        // ---- DECIDIR SI SE ENVÍA O NO ----
        if (!todoBien) {

            // preventDefault() CANCELA el envío del formulario.
            //
            // Sin esta línea, el formulario se enviaría igual y los
            // mensajes de error desaparecerían al recargar la página.
            // Es la línea que hace que toda esta función sirva de algo.
            evento.preventDefault();

            // Llevar al usuario al primer campo con error.
            // Si el error está en un campo que quedó fuera de la
            // pantalla, el usuario vería que "no pasa nada" al pulsar
            // el botón, sin entender por qué.
            const primerError = formulario.querySelector('.campo-error');
            if (primerError) {
                // scrollIntoView desliza la página hasta el elemento.
                // behavior: 'smooth' lo hace suave en vez de un salto.
                // block: 'center' lo deja en el centro de la pantalla.
                primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // focus() pone el cursor dentro del campo, listo para
                // corregir.
                primerError.focus();
            }
        }

        // Si todoBien quedó en true, no hacemos nada y el formulario
        // se envía normalmente. Ahí lo recibe PHP y lo vuelve a
        // validar — porque, recordemos, esta validación es solo una
        // cortesía para el usuario, no una barrera de seguridad.
    });


    /* -----------------------------------------------------------------
       LIMPIAR EL ERROR MIENTRAS EL USUARIO CORRIGE
       -----------------------------------------------------------------
       Detalle pequeño que mejora mucho la experiencia: apenas el
       usuario empieza a escribir en un campo marcado en rojo, se le
       quita la marca.

       Sin esto, el campo se quedaría rojo hasta que vuelva a pulsar
       "Guardar", y el usuario sentiría que el sistema no reacciona.

       El evento 'input' se dispara con CADA tecla que el usuario
       presiona.
       ----------------------------------------------------------------- */
    ['nombre', 'talla', 'precio', 'stock'].forEach(function (id) {
        const campo = document.getElementById(id);

        // Siempre revisar que el campo exista antes de usarlo
        if (campo) {
            campo.addEventListener('input', function () {
                // Solo limpiamos si el campo está marcado como erróneo
                if (campo.classList.contains('campo-error')) {
                    limpiarError(campo);
                }
            });
        }
    });

});
