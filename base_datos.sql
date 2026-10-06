-- =====================================================================
--  PROYECTO : MVP Ventas de Ropa
--  ARCHIVO  : base_datos.sql
--  ¿QUÉ ES? : Este archivo es el "plano" de la base de datos. Contiene
--             las instrucciones SQL que crean la base de datos, sus
--             tablas y unos datos de ejemplo para poder probar.
--
--  ¿CÓMO SE USA?
--    1. Abrir phpMyAdmin (http://localhost/phpmyadmin)
--    2. Pestaña "Importar"
--    3. Seleccionar este archivo y pulsar "Continuar"
--
--  SENA · CIAS Regional Boyacá · Programación de Software (233104)
--  Ficha 3168431 · RAP 03 — Codificar el software · Comp. 220501096
--  Instructor: Manuel Alejandro Ordóñez Hernández
-- =====================================================================


-- ---------------------------------------------------------------------
-- PASO 0: Fijar el juego de caracteres de ESTA importación
-- ---------------------------------------------------------------------
-- Esta línea es corta pero evita un problema muy difícil de diagnosticar.
--
-- EL PROBLEMA:
-- Crear la base de datos en utf8mb4 (paso 1) no basta. Falta decirle al
-- programa que está importando este archivo en qué juego de caracteres
-- está escrito. Si no se le dice, cada programa asume lo que quiere:
-- phpMyAdmin suele acertar, pero la consola de MySQL no, y reinterpreta
-- los bytes.
--
-- ¿CÓMO SE VE EL DAÑO?
-- "Dr. Peña" se guarda como "Dr. PeÃ±a", y "Pantalón" como "PantalÃ³n".
-- A eso se le dice "mojibake": texto que pasó por dos codificaciones
-- distintas y quedó ilegible.
--
-- Y lo peor es que NO es solo un problema visual: si el texto guardado
-- tiene bytes distintos a los que manda PHP, las comparaciones fallan.
-- Una búsqueda de "Peña" no encuentra nada, y una restricción UNIQUE
-- deja pasar duplicados que debería bloquear.
--
-- SET NAMES utf8mb4 = "todo lo que viene en este archivo está escrito
-- en utf8mb4, interprétalo así". Con esto el archivo se importa igual
-- de bien desde phpMyAdmin, desde la consola o desde cualquier
-- herramienta.
-- ---------------------------------------------------------------------
SET NAMES utf8mb4;


-- ---------------------------------------------------------------------
-- PASO 1: Crear la base de datos
-- ---------------------------------------------------------------------
-- CREATE DATABASE  = "crea una base de datos"
-- IF NOT EXISTS    = "solo si no existe ya" (evita el error si la corremos
--                     dos veces por equivocación)
-- CHARACTER SET utf8mb4 = el juego de caracteres. utf8mb4 es el que
--                     permite guardar tildes (á, é), la letra ñ y emojis.
--                     Si usáramos el juego por defecto, "Camisón" se
--                     guardaría como "CamisÃ³n".
-- COLLATE          = las reglas para comparar y ordenar texto.
--                    unicode_ci: el "ci" significa case-insensitive, o sea
--                    que al buscar, "CAMISA" y "camisa" se consideran
--                    iguales.
-- ---------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS mvp_ropa
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- USE = "de aquí en adelante, trabaja dentro de esta base de datos".
-- Sin esta línea habría que escribir mvp_ropa.prendas en cada consulta.
USE mvp_ropa;


-- ---------------------------------------------------------------------
-- PASO 2: Tabla USUARIOS  (para el login)
-- ---------------------------------------------------------------------
-- Esta tabla guarda quién puede entrar al sistema. Es la tabla que hace
-- funcionar el login mínimo que se acordó para el proyecto.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (

    -- id: el número que identifica de forma única a cada usuario.
    --   INT            = número entero
    --   AUTO_INCREMENT = la base de datos lo asigna sola: 1, 2, 3...
    --                    Nunca lo escribimos nosotros.
    --   PRIMARY KEY    = es "la llave" de la tabla. No se puede repetir
    --                    y no puede quedar vacío.
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- usuario: el nombre con el que se inicia sesión.
    --   VARCHAR(40) = texto de máximo 40 caracteres.
    --   NOT NULL    = obligatorio, no puede quedar vacío.
    --   UNIQUE      = no pueden existir dos usuarios con el mismo nombre.
    usuario VARCHAR(40) NOT NULL UNIQUE,

    -- clave: la contraseña, pero GUARDADA CIFRADA (hasheada).
    --   Es VARCHAR(255) y no VARCHAR(20) porque NO guardamos la
    --   contraseña que el usuario escribe, sino el resultado de
    --   password_hash() de PHP, que es un texto largo tipo:
    --   $2y$10$N9qo8uLOickgx2ZMRZoMy...
    --   Si alguien roba la base de datos, no puede leer las contraseñas.
    clave VARCHAR(255) NOT NULL,

    -- rol: para qué está autorizado este usuario.
    --   DEFAULT 'auxiliar' = si no se especifica, entra como auxiliar.
    rol VARCHAR(20) NOT NULL DEFAULT 'auxiliar',

    -- creado_en: cuándo se registró este usuario.
    --   TIMESTAMP                 = fecha y hora
    --   DEFAULT CURRENT_TIMESTAMP = se llena sola con la fecha de hoy
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ENGINE=InnoDB = el motor de almacenamiento. InnoDB es el que soporta
--                 transacciones y llaves foráneas. Es el estándar actual.


-- ---------------------------------------------------------------------
-- PASO 3: Tabla PRENDAS  (la tabla principal del proyecto)
-- ---------------------------------------------------------------------
-- Aquí vive la información del negocio: cada fila es una prenda del
-- inventario de la tienda.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS prendas (

    -- Identificador único de la prenda
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- nombre: cómo se llama la prenda. Ej: "Camiseta básica"
    nombre VARCHAR(60) NOT NULL,

    -- talla: S, M, L, XL... Por eso solo 5 caracteres.
    talla VARCHAR(5) NOT NULL,

    -- color: NULL permitido porque hay prendas sin color definido
    -- (estampadas, multicolor). Por eso NO lleva NOT NULL.
    color VARCHAR(30) NULL,

    -- precio: DECIMAL(10,2) y NO float.
    --   10 = cantidad total de dígitos
    --    2 = cuántos van después del punto decimal
    --   Ej: 45000.00  →  admite hasta 99.999.999,99
    --
    --   ¿Por qué DECIMAL y no FLOAT? Porque FLOAT guarda números
    --   aproximados y con dinero eso produce errores de centavos.
    --   Con dinero SIEMPRE se usa DECIMAL.
    precio DECIMAL(10,2) NOT NULL,

    -- stock: unidades disponibles. INT porque no existe media prenda.
    stock INT NOT NULL DEFAULT 0,

    -- imagen_url: la dirección web de la foto de la prenda.
    --   Se usa una URL (y no subir el archivo) porque así lo acordamos
    --   para todos los equipos. Subir el archivo es el RETO opcional
    --   de este proyecto.
    imagen_url VARCHAR(255) NULL,

    -- creado_en: cuándo se registró la prenda
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- PASO 4: Usuario inicial para poder entrar la primera vez
-- ---------------------------------------------------------------------
-- Si la tabla usuarios queda vacía, nadie podría iniciar sesión nunca.
-- Por eso insertamos un usuario administrador de arranque.
--
--   Usuario    : admin
--   Contraseña : admin123
--
-- El texto largo que aparece abajo es el resultado de aplicarle
-- password_hash('admin123', PASSWORD_DEFAULT) en PHP. NO es la
-- contraseña: es su huella cifrada, y no se puede devolver al original.
--
-- ¡IMPORTANTE PARA LA SUSTENTACIÓN! Esta contraseña es de práctica.
-- En un sistema real se cambia antes de ponerlo en producción.
-- ---------------------------------------------------------------------
INSERT INTO usuarios (usuario, clave, rol)
SELECT 'admin', '$2y$12$pxSU4StAhJPBcgyQhDlpXe6sBavafrwiXoMZOm3D7U3stPe26Pb0m', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE usuario = 'admin');
-- El "WHERE NOT EXISTS" evita crear el admin dos veces si el archivo
-- se importa más de una vez.


-- ---------------------------------------------------------------------
-- PASO 5: Datos de ejemplo
-- ---------------------------------------------------------------------
-- Sirven para que al abrir el sistema por primera vez ya se vea algo en
-- pantalla y se puedan probar la búsqueda, la edición y las estadísticas.
--
-- ¿POR QUÉ ESTE INSERT SE VE TAN RARO, CON UN SELECT ADENTRO?
--
-- Porque está protegido contra la importación repetida. Un INSERT
-- normal se ejecuta siempre. Si alguien importa este archivo dos veces
-- por equivocación, terminaría con doce prendas en vez de seis, todas
-- duplicadas, y creería que el sistema está dañado.
--
-- El truco: en vez de insertar valores fijos, insertamos el RESULTADO
-- de un SELECT que solo devuelve filas cuando la tabla está vacía.
--
--     WHERE NOT EXISTS (SELECT 1 FROM prendas)
--
-- se lee: "solo si NO existe ninguna fila en prendas".
--
-- Si la tabla ya tiene datos, el SELECT no devuelve nada y el INSERT
-- no inserta nada. Importar el archivo diez veces da el mismo
-- resultado que importarlo una. A eso se le dice que la operación es
-- "repetible sin daño".
--
-- El "FROM DUAL" es una tabla ficticia de MySQL que sirve justamente
-- para estos casos: cuando se necesita un SELECT que no lee de ninguna
-- tabla real.
-- ---------------------------------------------------------------------
INSERT INTO prendas (nombre, talla, color, precio, stock, imagen_url)
SELECT * FROM (
    SELECT 'Camiseta básica'      AS n, 'M'  AS t, 'Azul'   AS c,  45000.00 AS p, 12 AS s, NULL AS i
    UNION ALL SELECT 'Jean clásico',         'L',  'Negro',  120000.00,  5, NULL
    UNION ALL SELECT 'Blusa manga larga',    'S',  'Blanco',  68000.00,  8, NULL
    UNION ALL SELECT 'Chaqueta impermeable', 'XL', 'Verde',  185000.00,  3, NULL
    UNION ALL SELECT 'Camisa a cuadros',     'M',  'Rojo',    72000.00,  0, NULL
    UNION ALL SELECT 'Pantalón deportivo',   'L',  'Gris',    95000.00, 15, NULL
) AS ejemplos
WHERE NOT EXISTS (SELECT 1 FROM prendas);


-- ---------------------------------------------------------------------
-- VERIFICACIÓN — se puede correr para comprobar que todo quedó bien
-- ---------------------------------------------------------------------
-- Estas consultas están comentadas y SIN punto y coma al final, a
-- propósito. Para usarlas: cópialas a la pestaña SQL de phpMyAdmin,
-- quítales los guiones del principio y agrégales el punto y coma.
--
-- ¿Por qué sin punto y coma? Porque phpMyAdmin parte el archivo en
-- pedazos cada vez que ve un punto y coma, INCLUSO dentro de un
-- comentario. Un
-- pedazo que queda con puro comentario no tiene nada que ejecutar, y
-- phpMyAdmin lo reporta como 'Error #1046: base de datos no
-- seleccionada'. No rompe nada, pero asusta y hace creer que la
-- importación falló cuando en realidad salió bien.
-- ---------------------------------------------------------------------
-- SELECT COUNT(*) AS total_prendas  FROM prendas   -- debe dar 6
-- SELECT COUNT(*) AS total_usuarios FROM usuarios  -- debe dar 1
-- ---------------------------------------------------------------------
