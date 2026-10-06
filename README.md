# MVP Ventas de Ropa

Sistema web para administrar el inventario de una tienda de ropa.

Proyecto formativo desarrollado en el **RAP 03 — Codificar el software**
(competencia 220501096) del programa Técnico en Programación de Software.

**SENA · Centro de la Innovación Agroindustrial y de Servicios (CIAS) · Regional Boyacá**
Ficha 3168431 · I.E. San Pedro Claver
Instructor: Manuel Alejandro Ordóñez Hernández

---

## Qué hace el sistema

- Inicio de sesión con contraseña cifrada
- Registrar, consultar, actualizar y eliminar prendas (CRUD completo)
- Buscar prendas por nombre o por color
- Ver el detalle de cada prenda con su fotografía
- Estadísticas del inventario: valor total, distribución por talla y alerta de prendas por agotarse
- Conversión del precio a dólares consultando una API REST pública
- Pruebas automáticas de las funciones del sistema

---

## Requisitos

| Herramienta | Versión | Para qué sirve |
|---|---|---|
| XAMPP | 8.x | Trae Apache, PHP y MySQL en un solo instalador |
| PHP | 8.0 o superior | Ejecuta el código del servidor |
| MySQL / MariaDB | 10.x | Guarda los datos |
| Navegador | Cualquiera actual | Para usar el sistema |
| Conexión a internet | Opcional | Solo para Bootstrap y la API del dólar |

> **Sin internet el sistema funciona igual.** La página se verá sin
> estilos y el precio en dólares mostrará un valor aproximado con su
> aviso, pero el CRUD completo sigue operando.

**Revisa el puerto de MySQL antes de empezar.** El valor normal es el
3306 y así viene configurado el proyecto. Si tu XAMPP quedó en otro
puerto, cámbialo en `config/conexion.php`.

> Para ver el tuyo: panel de XAMPP → botón **Config** de MySQL →
> `my.ini` → sección `[mysqld]` → la línea `port=`.
> Si el puerto está mal, sale *"Connection refused"* aunque MySQL esté
> prendido y en verde.

---

## Instalación en 6 pasos

### 1. Copiar el proyecto a htdocs

Descomprime o clona el proyecto dentro de la carpeta `htdocs` de XAMPP:

```
C:\xampp\htdocs\mvp-ventas-ropa\
```

Si usas Git:

```bash
cd C:\xampp\htdocs
git clone https://github.com/TU-USUARIO/mvp-ventas-ropa.git
```

### 2. Encender Apache y MySQL

Abre el panel de control de XAMPP y pulsa **Start** en Apache y en MySQL.
Los dos deben quedar en verde.

### 3. Crear la base de datos

1. Entra a `http://localhost/phpmyadmin`
2. Pestaña **Importar**
3. **Seleccionar archivo** → busca `base_datos.sql`
4. Pulsa **Continuar**

Debe crearse la base `mvp_ropa` con las tablas `prendas` y `usuarios`.

### 4. Configurar la conexión

> **Si descomprimiste el ZIP:** el archivo `config/conexion.php` ya viene
> listo con los valores de XAMPP (root, sin contraseña, puerto 3306).
> Solo verifica que el puerto coincida con el tuyo y pasa al paso 5.
>
> **Si clonaste de GitHub:** el archivo no existe (está bloqueado en
> `.gitignore`, por seguridad). Tienes que crearlo con los pasos de abajo.

Copia el archivo de ejemplo y ponle el nombre real:

```bash
cd config
copy conexion.ejemplo.php conexion.php     # Windows
cp   conexion.ejemplo.php conexion.php     # Linux o Mac
```

Abre `config/conexion.php` y escribe tus datos:

```php
define('DB_SERVIDOR', '127.0.0.1');
define('DB_USUARIO',  'root');   // en XAMPP normalmente es root
define('DB_CLAVE',    '');       // en XAMPP normalmente va vacía
define('DB_NOMBRE',   'mvp_ropa');
define('DB_PUERTO',   3306);     // revisa el tuyo en XAMPP
```

> **Este archivo nunca se sube a GitHub.** Ya está bloqueado en
> `.gitignore` porque contiene la contraseña de la base de datos.

### 5. Abrir el sistema

```
http://localhost/mvp-ventas-ropa/
```

### 6. Entrar

| Usuario | Contraseña |
|---|---|
| `admin` | `admin123` |

> Esta contraseña es solo de práctica. En un sistema real se cambia
> antes de ponerlo a funcionar.

---

## Estructura del proyecto

```
mvp-ventas-ropa/
│
├── config/
│   ├── conexion.php           Datos de conexión reales — NO se sube
│   └── conexion.ejemplo.php   Plantilla sin datos — SÍ se sube
│
├── includes/                  Componentes reutilizables
│   ├── sesion.php             Control de acceso (el "portero")
│   ├── funciones.php          Validación, limpieza y formato
│   ├── api_dolar.php          Consumo de la API REST (Guía 2)
│   ├── cabecera.php           Parte de arriba de todas las páginas
│   └── pie.php                Parte de abajo de todas las páginas
│
├── assets/                    Recursos estáticos
│   ├── css/estilos.css        Estilos propios
│   ├── js/validaciones.js     Validación en el navegador
│   └── img/                   Imágenes
│
├── index.php                  Puerta de entrada (solo redirige)
│
├── registrar.php              Formulario de alta        (CREATE 1/2)
├── guardar.php                Inserta en la base        (CREATE 2/2)
├── consultar.php              Listado y búsqueda        (READ)
├── editar.php                 Formulario precargado     (UPDATE 1/2)
├── actualizar.php             Aplica el cambio          (UPDATE 2/2)
├── eliminar.php               Confirma y elimina        (DELETE)
│
├── detalle.php                Ficha completa + API del dólar
├── estadisticas.php           Consulta propia del proyecto
├── login.php                  Inicio de sesión
├── logout.php                 Cierre de sesión
├── pruebas.php                Pruebas automáticas (Guía 2)
│
├── base_datos.sql             Estructura de la base de datos
├── .gitignore                 Archivos que Git debe ignorar
├── README.md                  Este archivo
├── MANUAL_DE_USUARIO.md       Manual para quien usa el sistema
└── INSTALACION.html           Guía visual de instalación
```

**Por qué crear y actualizar llevan dos archivos cada uno:** porque cada
archivo hace UNA sola cosa. Uno muestra el formulario, el otro escribe en
la base de datos. Así, cuando algo falla, se sabe de una vez dónde buscar:
si el formulario se ve mal, el problema está en `registrar.php`; si el dato
no se guarda, está en `guardar.php`. Es la misma regla que se aplicó a las
carpetas.

Las carpetas siguen la separación que pide la guía:
**configuración** (`config/`), **componentes reutilizables** (`includes/`)
y **recursos** (`assets/`).

---

## Base de datos

### Tabla `prendas`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT AUTO_INCREMENT | Llave primaria |
| `nombre` | VARCHAR(60) | Nombre de la prenda |
| `talla` | VARCHAR(5) | XS, S, M, L, XL, XXL |
| `color` | VARCHAR(30) | Opcional |
| `precio` | DECIMAL(10,2) | En pesos. DECIMAL y no FLOAT, por precisión |
| `stock` | INT | Unidades disponibles |
| `imagen_url` | VARCHAR(255) | Dirección web de la foto, opcional |
| `creado_en` | TIMESTAMP | Se llena automáticamente |

### Tabla `usuarios`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT AUTO_INCREMENT | Llave primaria |
| `usuario` | VARCHAR(40) UNIQUE | Nombre de acceso |
| `clave` | VARCHAR(255) | Hash `password_hash()`, nunca texto plano |
| `rol` | VARCHAR(20) | admin o auxiliar |
| `creado_en` | TIMESTAMP | Se llena automáticamente |

---

## Cómo se protegió el sistema

| Amenaza | Defensa aplicada | Dónde verla |
|---|---|---|
| Inyección SQL | Consultas preparadas con `bind_param()` en **toda consulta que reciba un dato del usuario** | cualquier archivo con SQL |
| XSS | `htmlspecialchars()` en todo dato que se imprime, vía `limpiar()` | `includes/funciones.php` |
| Contraseñas robadas | `password_hash()` y `password_verify()` | `login.php` |
| Acceso sin permiso | `exigir_sesion()` en todas las páginas privadas | `includes/sesion.php` |
| Fijación de sesión | `session_regenerate_id(true)` al iniciar sesión | `login.php` |
| Doble envío (F5) | Patrón PRG: redirigir después de guardar | `guardar.php`, `actualizar.php` |
| Borrado accidental | Confirmación obligatoria, borrado solo por POST | `eliminar.php` |
| Datos inválidos |  Validación en el servidor, no solo en el navegador | `includes/funciones.php` |

---

## Ejecutar las pruebas

```
http://localhost/mvp-ventas-ropa/pruebas.php
```

O desde la terminal:

```bash
php pruebas.php
```

Son **29 verificaciones** sobre las funciones del sistema: limpieza de
datos, formato de precios, reglas de validación, límites del stock y
conversión de moneda. Todas deben pasar.

---

## La API REST (Guía 2)

El sistema consulta la tasa del dólar para mostrar los precios también
en esa moneda.

- **Servicio:** `https://open.er-api.com/v6/latest/USD`
- **Gratuita**, sin registro ni clave
- **Dónde se ve:** en `detalle.php`, tarjeta "Precio en dólares"
- **Código:** `includes/api_dolar.php`

Tres decisiones de diseño que vale la pena sustentar:

1. **Límite de tiempo de 5 segundos.** Si la API no responde, el
   sistema no se queda colgado esperando.
2. **Caché de 1 hora.** No se consulta el servicio en cada recarga:
   es más rápido y no se abusa de un servicio gratuito ajeno.
3. **Valor de respaldo.** Si la API falla, se muestra un valor
   aproximado con su aviso, en vez de romper la página.

---

## Reto pendiente del equipo

Cambiar el campo de dirección de imagen por una **subida de archivo
real**, con:

- validación del tipo de archivo (solo imágenes)
- límite de tamaño
- nombre único para evitar que un archivo sobrescriba a otro

---

## Equipo

Proyecto desarrollado por el equipo de **Ventas de Ropa** (6 aprendices),
ficha 3168431.
