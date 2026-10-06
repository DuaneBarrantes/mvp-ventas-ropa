<?php
/**
 * =====================================================================
 *  ARCHIVO  : includes/api_dolar.php
 *  PROYECTO : MVP Ventas de Ropa
 *  GUÍA     : 2 — Consumo de API REST pública
 * ---------------------------------------------------------------------
 *  ¿QUÉ HACE ESTE ARCHIVO?
 *  Consulta por internet cuánto vale el dólar hoy, para poder mostrar
 *  el precio de las prendas también en dólares, además de en pesos.
 *
 *  ¿QUÉ ES UNA API REST? (pregunta clave de la sustentación)
 *
 *  Una API es un servicio de otro sistema al que le podemos pedir datos
 *  desde nuestro programa. "REST" es el estilo más común de hacerlo:
 *  uno visita una dirección web y, en vez de devolver una página bonita
 *  para leer, devuelve DATOS en bruto para que los use un programa.
 *
 *  La diferencia con una página normal:
 *     Página web  → devuelve HTML  → para que lo LEA una persona
 *     API REST    → devuelve JSON  → para que lo USE un programa
 *
 *  ¿QUÉ ES JSON?
 *  Es un formato de texto para organizar datos. Se ve así:
 *      {"rates": {"COP": 4150.25}, "base": "USD"}
 *  Los { } son objetos, los : separan nombre y valor. PHP lo convierte
 *  en un arreglo con json_decode() y ya lo puede usar normalmente.
 *
 *  ¿QUÉ RECIBE?  nada
 *  ¿QUÉ ENTREGA? un arreglo con:
 *      'ok'     → true si se pudo consultar, false si no
 *      'valor'  → cuántos pesos vale un dólar
 *      'fuente' → de dónde salió el dato
 *      'error'  → el mensaje, si algo falló
 * ---------------------------------------------------------------------
 *  SENA · CIAS Regional Boyacá · Programación de Software (233104)
 *  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
 * =====================================================================
 */


/* ---------------------------------------------------------------------
 * CONFIGURACIÓN
 * --------------------------------------------------------------------- */

// La dirección de la API que vamos a consultar.
// Esta API es gratuita y no pide registro ni clave, por eso se eligió
// para clase. Devuelve las tasas de cambio tomando el dólar como base.
define('API_DOLAR_URL', 'https://open.er-api.com/v6/latest/USD');

// Cuántos segundos esperamos como máximo antes de rendirnos.
//
// ¿POR QUÉ ES IMPORTANTE? Porque si la API está caída o el internet
// está lento, SIN este límite nuestra página se quedaría cargando para
// siempre y el usuario no podría ni ver su inventario.
//
// REGLA: un servicio externo NUNCA puede bloquear tu sistema.
define('API_DOLAR_TIMEOUT', 5);

// Cuántos segundos guardamos el dato antes de volver a preguntar.
// 3600 segundos = 1 hora.
define('API_DOLAR_CACHE_SEG', 3600);

// Valor de respaldo, por si la API no responde.
// Así el sistema sigue funcionando (mostrando un aproximado y
// avisando que es aproximado) en vez de romperse.
define('API_DOLAR_RESPALDO', 4100.00);


/**
 * FUNCIÓN: obtener_tasa_dolar()
 * ---------------------------------------------------------------------
 * Consulta la API y devuelve cuántos pesos colombianos vale un dólar.
 */
function obtener_tasa_dolar(): array
{
    /* -----------------------------------------------------------------
     * PASO 1 — REVISAR LA CACHÉ
     * -----------------------------------------------------------------
     * ¿QUÉ ES LA CACHÉ? Es guardar el resultado para no tener que
     * volver a pedirlo.
     *
     * ¿POR QUÉ SE HACE?
     *   1. Velocidad: leer un archivo del disco tarda milésimas;
     *      consultar internet tarda segundos.
     *   2. Respeto: si 21 aprendices recargan la página 50 veces, serían
     *      1.050 consultas a un servicio gratuito ajeno. Muchas APIs
     *      bloquean por abuso.
     *   3. La tasa del dólar no cambia cada segundo: una hora de
     *      antigüedad es perfectamente aceptable.
     * ----------------------------------------------------------------- */

    // sys_get_temp_dir() devuelve la carpeta de archivos temporales del
    // sistema. Funciona igual en Windows, Linux y Mac, por eso se usa
    // en vez de escribir una ruta fija como C:\temp
    $archivo_cache = sys_get_temp_dir() . '/mvp_ropa_dolar.json';

    // file_exists  = ¿existe el archivo?
    // filemtime    = ¿cuándo se modificó por última vez?
    // time()       = ¿qué hora es ahora? (en segundos)
    //
    // Si la diferencia es menor al tiempo de caché, el dato aún sirve.
    if (file_exists($archivo_cache)
        && (time() - filemtime($archivo_cache)) < API_DOLAR_CACHE_SEG) {

        // Leemos el archivo y lo convertimos de JSON a arreglo.
        // El segundo parámetro "true" de json_decode hace que devuelva
        // un ARREGLO. Sin ese true, devolvería un objeto.
        $guardado = json_decode(file_get_contents($archivo_cache), true);

        // is_array verifica que el archivo no esté dañado o vacío
        if (is_array($guardado) && isset($guardado['valor'])) {
            $guardado['fuente'] = 'Dato guardado (se actualiza cada hora)';
            return $guardado;
        }
    }


    /* -----------------------------------------------------------------
     * PASO 2 — CONSULTAR LA API POR INTERNET
     * -----------------------------------------------------------------
     * Usamos cURL, que es la herramienta de PHP para hacer peticiones
     * a otros servidores. Es como un navegador sin ventana.
     *
     * ¿POR QUÉ NO file_get_contents(), QUE ES MÁS CORTO?
     * Porque con cURL se puede poner un límite de tiempo y leer el
     * código de respuesta. Con file_get_contents, si el servidor no
     * responde, la página se queda colgada. Para consumir APIs siempre
     * se usa cURL.
     * ----------------------------------------------------------------- */

    // curl_init() prepara la petición hacia esa dirección
    $ch = curl_init(API_DOLAR_URL);

    // curl_setopt_array configura varias opciones de una vez
    curl_setopt_array($ch, [

        // true = "devuélveme la respuesta como texto" en vez de
        // imprimirla directamente en pantalla. Sin esta opción, el JSON
        // aparecería tirado en medio de nuestra página.
        CURLOPT_RETURNTRANSFER => true,

        // Límite de segundos para toda la operación
        CURLOPT_TIMEOUT => API_DOLAR_TIMEOUT,

        // Límite de segundos solo para lograr la conexión inicial
        CURLOPT_CONNECTTIMEOUT => API_DOLAR_TIMEOUT,

        // Seguir redirecciones si el servidor nos manda a otra dirección
        CURLOPT_FOLLOWLOCATION => true,

        // Verificar el certificado de seguridad del sitio (HTTPS).
        //
        // ¡NUNCA PONGAS ESTO EN false! En muchos tutoriales de internet
        // aparece en false "para que funcione", pero eso desactiva la
        // verificación y permite que alguien se haga pasar por la API.
        // Si da error de certificado, se soluciona instalando el
        // certificado, no apagando la verificación.
        CURLOPT_SSL_VERIFYPEER => true,

        // Identificarnos ante el servidor. Es buena educación en
        // internet: el dueño de la API sabe quién lo está consultando.
        CURLOPT_USERAGENT => 'MVP-VentasRopa-SENA/1.0',
    ]);

    // curl_exec ejecuta la petición. Devuelve el texto de la respuesta,
    // o false si falló.
    $respuesta = curl_exec($ch);

    // curl_getinfo con CURLINFO_HTTP_CODE trae el código de respuesta:
    //     200 = todo bien
    //     404 = no se encontró
    //     500 = error del servidor
    //     0   = ni siquiera se logró conectar (sin internet)
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Guardamos el mensaje de error de cURL, si lo hubo
    $error_curl = curl_error($ch);

    // Cerrar siempre, para liberar la conexión
    curl_close($ch);


    /* -----------------------------------------------------------------
     * PASO 3 — REVISAR SI LA RESPUESTA SIRVE
     * -----------------------------------------------------------------
     * Aquí está el corazón del manejo de errores. Hay que revisar TRES
     * cosas distintas, porque cada una puede fallar por su lado:
     *   a) ¿Hubo conexión?
     *   b) ¿El servidor respondió con éxito (código 200)?
     *   c) ¿Lo que devolvió es un JSON válido con el dato que espero?
     *
     * Un error típico de principiante es revisar solo (a) y asumir que
     * si hubo respuesta, el dato está. Pero un servidor puede responder
     * perfectamente... con un mensaje de error.
     * ----------------------------------------------------------------- */

    // (a) y (b): ¿falló la conexión o el servidor no respondió 200?
    if ($respuesta === false || $codigo !== 200) {
        return [
            'ok'     => false,
            'valor'  => API_DOLAR_RESPALDO,
            'fuente' => 'Valor aproximado (no se pudo consultar la tasa real)',
            'error'  => $error_curl !== ''
                        ? $error_curl
                        : 'El servicio respondió con el código ' . $codigo,
        ];
    }

    // (c) Convertir el texto JSON en un arreglo de PHP
    $datos = json_decode($respuesta, true);

    // json_last_error() nos dice si el texto era JSON válido.
    // JSON_ERROR_NONE significa "sin errores".
    //
    // ¿Cuándo pasa esto? Cuando el servidor devuelve una página de
    // error en HTML en vez del JSON esperado. Si no revisáramos,
    // $datos quedaría en null y la línea siguiente fallaría.
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'ok'     => false,
            'valor'  => API_DOLAR_RESPALDO,
            'fuente' => 'Valor aproximado (la respuesta no era válida)',
            'error'  => 'El servicio no devolvió datos en formato JSON.',
        ];
    }

    // ¿Viene la casilla que necesitamos?
    //
    // La respuesta de esta API tiene esta estructura:
    //     { "result": "success",
    //       "base_code": "USD",
    //       "rates": { "COP": 4150.25, "EUR": 0.92, ... } }
    //
    // O sea que el dato que queremos está DENTRO de "rates", en la
    // casilla "COP". Por eso se escribe  $datos['rates']['COP']
    if (!isset($datos['rates']['COP'])) {
        return [
            'ok'     => false,
            'valor'  => API_DOLAR_RESPALDO,
            'fuente' => 'Valor aproximado (no vino la tasa del peso)',
            'error'  => 'La respuesta no incluye la tasa para COP.',
        ];
    }


    /* -----------------------------------------------------------------
     * PASO 4 — ¡TODO BIEN! Armar el resultado y guardarlo en caché
     * ----------------------------------------------------------------- */

    $resultado = [
        'ok'     => true,
        // (float) convierte a número con decimales
        'valor'  => (float) $datos['rates']['COP'],
        'fuente' => 'Tasa consultada en línea',
        'error'  => '',
    ];

    // Guardar en caché para la próxima vez.
    // @ delante de la función silencia el aviso si no se puede escribir
    // (por ejemplo, sin permisos en la carpeta temporal).
    //
    // ¿Por qué silenciarlo? Porque NO poder guardar la caché no es
    // grave: solo significa que la próxima vez se volverá a consultar.
    // No vale la pena romper la página por eso.
    @file_put_contents($archivo_cache, json_encode($resultado));

    return $resultado;
}


/**
 * FUNCIÓN: pesos_a_dolares()
 * ---------------------------------------------------------------------
 *  RECIBE  : $pesos — el precio en pesos colombianos
 *            $tasa  — cuántos pesos vale un dólar
 *  DEVUELVE: el precio en dólares, ya formateado: "US$ 10.84"
 *
 *  La cuenta es una división simple: si el dólar está a 4.150 y la
 *  prenda cuesta 45.000 pesos, entonces:
 *      45000 / 4150 = 10.84 dólares
 */
function pesos_a_dolares(float $pesos, float $tasa): string
{
    // PROTECCIÓN CONTRA DIVISIÓN POR CERO.
    //
    // Si por algún motivo la tasa llegara en 0, dividir entre 0 en PHP 8
    // lanza un error fatal (DivisionByZeroError) y tumba la página.
    //
    // Siempre que se vaya a dividir por una variable, hay que
    // preguntarse: "¿esto puede llegar a ser cero?". Si la respuesta
    // es sí, hay que revisarlo antes.
    if ($tasa <= 0) {
        return 'US$ —';
    }

    $dolares = $pesos / $tasa;

    // number_format con 2 decimales, punto decimal y coma de miles
    // (así se escribe la moneda en Estados Unidos)
    return 'US$ ' . number_format($dolares, 2, '.', ',');
}
