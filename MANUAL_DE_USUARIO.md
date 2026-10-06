# Manual de usuario
## Sistema de Inventario — Ventas de Ropa

---

**Para quién es este manual:** para la persona que va a usar el sistema
en la tienda. No hace falta saber de programación ni de computadores.

**Proyecto formativo SENA** · CIAS Regional Boyacá
Programación de Software (233104) · Ficha 3168431
RAP 03 — Codificar el software · Competencia 220501096

---

## Contenido

1. [Qué es este sistema](#1-qué-es-este-sistema)
2. [Cómo entrar](#2-cómo-entrar)
3. [La pantalla principal](#3-la-pantalla-principal)
4. [Registrar una prenda nueva](#4-registrar-una-prenda-nueva)
5. [Buscar una prenda](#5-buscar-una-prenda)
6. [Ver el detalle de una prenda](#6-ver-el-detalle-de-una-prenda)
7. [Modificar una prenda](#7-modificar-una-prenda)
8. [Eliminar una prenda](#8-eliminar-una-prenda)
9. [Ver las estadísticas](#9-ver-las-estadísticas)
10. [Cerrar sesión](#10-cerrar-sesión)
11. [Preguntas frecuentes](#11-preguntas-frecuentes)
12. [Qué hacer si algo sale mal](#12-qué-hacer-si-algo-sale-mal)

---

## 1. Qué es este sistema

Es un programa para llevar el control del inventario de una tienda de
ropa. Con él se puede saber, en cualquier momento:

- Qué prendas hay y cuántas unidades quedan de cada una
- Cuánto dinero hay invertido en mercancía
- Qué prendas están por agotarse y hay que volver a pedir
- Cuánto cuesta cada prenda en pesos y en dólares

Reemplaza el cuaderno o el archivo de Excel donde se llevaba la cuenta
a mano.

---

## 2. Cómo entrar

**Paso 1.** Abre el navegador y escribe esta dirección:

```
http://localhost/mvp-ventas-ropa/
```

**Paso 2.** Aparece la pantalla de entrada. Escribe tu usuario y tu
contraseña.

| Campo | Qué escribir |
|---|---|
| Usuario | `admin` |
| Contraseña | `admin123` |

**Paso 3.** Pulsa el botón **Entrar**.

> **Si dice "Usuario o contraseña incorrectos":** revisa que no tengas
> activada la tecla de mayúsculas (Bloq Mayús). La contraseña distingue
> entre mayúsculas y minúsculas.

> **Nota de seguridad:** esta contraseña viene puesta de fábrica para
> poder probar el sistema. Antes de usarlo de verdad en la tienda, hay
> que cambiarla.

---

## 3. La pantalla principal

Después de entrar verás el **Inventario**: la lista de todas las
prendas registradas.

### El menú de arriba

| Opción | Para qué sirve |
|---|---|
| **Ventas de Ropa** | Vuelve al inventario desde cualquier pantalla |
| **Inventario** | La lista de prendas |
| **Registrar prenda** | Agregar una prenda nueva |
| **Estadísticas** | Los reportes del negocio |
| **Cerrar sesión** | Salir del sistema |

### Las columnas de la tabla

| Columna | Qué muestra |
|---|---|
| **Prenda** | El nombre |
| **Talla** | XS, S, M, L, XL o XXL |
| **Color** | Un guion (—) si no se registró |
| **Precio** | En pesos colombianos |
| **Stock** | Las unidades que quedan, con color de alerta |
| **Acciones** | Los botones Ver, Editar y Eliminar |

### Los colores del stock

Este es el dato más útil de la pantalla. El color dice de un vistazo
qué tan urgente es reponer:

| Color | Qué significa | Qué hacer |
|---|---|---|
| 🟢 **Verde** | Más de 5 unidades | Nada, hay suficiente |
| 🟡 **Amarillo** | Entre 1 y 5 unidades | Ir pensando en pedir más |
| 🔴 **Rojo** — Agotado | No queda ninguna | Pedir ya, no hay para vender |

---

## 4. Registrar una prenda nueva

**Paso 1.** Pulsa el botón **+ Registrar prenda** (arriba a la derecha).

**Paso 2.** Llena el formulario:

| Campo | ¿Obligatorio? | Cómo llenarlo |
|---|---|---|
| **Nombre** | Sí | Cómo se llama. Ej: *Camiseta básica*. Máximo 60 letras |
| **Talla** | Sí | Se elige de la lista |
| **Color** | No | Ej: *Azul*. Se puede dejar vacío |
| **Precio** | Sí | Solo el número: `45000`. **Sin puntos ni comas** |
| **Stock** | Sí | Cuántas unidades hay. Número entero |
| **Imagen** | No | La dirección de una foto de internet |

**Paso 3.** Pulsa **Guardar prenda**.

Vuelves al inventario y aparece un **aviso verde** confirmando que se
guardó. La prenda nueva queda de primera en la lista.

### ⚠️ El error más común: el precio

**Escribe `45000`, no `45.000` ni `$45.000`.**

El sistema espera solo el número. Los puntos y el signo de pesos se los
pone él solo cuando muestra el precio.

| ✅ Correcto | ❌ Incorrecto |
|---|---|
| `45000` | `45.000` |
| `120000` | `$120.000` |
| `8500.50` | `8.500,50` |

### Si algo quedó mal

Aparece un **recuadro rojo** arriba diciendo exactamente qué corregir,
y el campo con problema se marca con borde rojo.

**Lo que ya escribiste no se pierde.** Solo corriges lo que está
marcado y vuelves a pulsar Guardar.

---

## 5. Buscar una prenda

Cuando hay muchas prendas, buscar es más rápido que leer toda la lista.

**Paso 1.** En la caja que dice *"Buscar por nombre o color..."*
escribe lo que buscas.

**Paso 2.** Pulsa **Buscar**.

### Cosas útiles de la búsqueda

- **Busca por nombre y por color al tiempo.** Si escribes "azul",
  encuentra las prendas que se llaman así Y las que son de ese color.
- **No necesitas la palabra completa.** Escribiendo "cami" ya aparecen
  las camisas y las camisetas.
- **No distingue mayúsculas.** "CAMISA" y "camisa" dan lo mismo.

**Para volver a ver todo:** pulsa el botón **Limpiar** que aparece al
lado.

> **Si no encuentra nada:** revisa que la palabra esté bien escrita. El
> sistema busca tal como escribiste, con tildes incluidas.

---

## 6. Ver el detalle de una prenda

Pulsa el botón **Ver** en la fila de la prenda.

Aquí se muestra todo lo que la lista no alcanza a mostrar:

- La **fotografía** en grande
- Todos los datos de la prenda
- El **valor en inventario**: cuánta plata representa esa prenda
  (precio × unidades), con la cuenta a la vista
- El **precio en dólares**, calculado con la tasa del día

### Sobre el precio en dólares

El sistema consulta por internet cuánto vale el dólar hoy.

| Lo que veas | Qué significa |
|---|---|
| Aviso **verde** | Se consultó la tasa real de hoy. El dato es confiable |
| Aviso **amarillo** | No se pudo consultar (sin internet o el servicio está caído). Se muestra un valor **aproximado** |

> Si sale el aviso amarillo, **el resto del sistema sigue funcionando
> normal**. Solo ese dato es aproximado.

---

## 7. Modificar una prenda

Se usa cuando cambia el precio, llega mercancía nueva o se corrige un
error de digitación.

**Paso 1.** Pulsa **Editar** en la fila de la prenda.

**Paso 2.** El formulario aparece **con los datos actuales ya escritos**.

**Paso 3.** Cambia solo lo que necesites.

**Paso 4.** Pulsa **Guardar cambios**.

> **Para reponer inventario:** cuando llega mercancía, edita la prenda y
> **suma** las unidades nuevas al stock que ya tenía. Si tenía 3 y
> llegaron 10, escribe `13`, no `10`.

Si te arrepientes, pulsa **Cancelar**: no se guarda nada.

---

## 8. Eliminar una prenda

> ### ⚠️ Esto no se puede deshacer
> Una prenda eliminada **no se puede recuperar**. Hay que registrarla
> otra vez desde cero.

El sistema te va a preguntar **dos veces**. No es que esté dañado: es a
propósito, porque esto no tiene vuelta atrás.

**Paso 1.** Pulsa **Eliminar** en la fila de la prenda.

**Paso 2.** Sale una ventanita del navegador preguntando *"¿Seguro que
quieres eliminar esta prenda?"*. Pulsa **Aceptar** para seguir, o
**Cancelar** para quedarte donde estabas.

**Paso 3.** Aparece una pantalla de confirmación **con los datos de la
prenda**. Léelos y verifica que sea la correcta — esta es la que de
verdad importa, porque aquí sí ves QUÉ vas a borrar.

**Paso 4.**

- Si es la correcta → **Sí, eliminar**
- Si te equivocaste → **Cancelar** (no pasa nada)

### ¿Eliminar o poner en cero?

Esta decisión importa para el negocio:

| Situación | Qué hacer |
|---|---|
| Se agotó pero la vas a volver a pedir | **Editar** y poner el stock en `0` |
| Ya no vas a vender más esa prenda | **Eliminar** |
| La registraste por error | **Eliminar** |

> Poner el stock en cero conserva el historial de la prenda. Eliminar
> lo borra todo.

---

## 9. Ver las estadísticas

Pulsa **Estadísticas** en el menú de arriba. Es el resumen del negocio.

### Las cuatro tarjetas de arriba

| Tarjeta | Qué te dice |
|---|---|
| **Modelos registrados** | Cuántas prendas DISTINTAS hay |
| **Unidades en bodega** | Cuántas prendas hay contando todas |
| **Valor del inventario** | Cuánta plata hay invertida en mercancía |
| **Precio promedio** | Lo que cuesta una prenda en promedio |

> **Modelos y unidades no son lo mismo.** Si tienes 3 modelos de
> camiseta y 10 unidades de cada uno, son **3 modelos** y
> **30 unidades**.

### Distribución por talla

Barras que muestran qué tallas tienes más surtidas. Sirve para darse
cuenta, por ejemplo, de que casi no quedan tallas L.

### Prendas por agotarse

**El reporte más útil de todos: es tu lista de compras.**

Muestra todo lo que tiene 5 unidades o menos, ordenado de lo más
urgente a lo menos urgente.

Cada fila trae un botón **Reabastecer** que te lleva directo a
actualizar el stock, sin tener que buscar la prenda en la lista.

### Las 5 prendas más costosas

Tu mercancía de mayor valor. Útil para decidir qué proteger y qué
destacar en la vitrina.

> **Para imprimir este reporte:** pulsa `Ctrl + P`. El menú y los
> botones no se imprimen: sale limpio, solo los datos.

---

## 10. Cerrar sesión

Pulsa **Cerrar sesión**, arriba a la derecha.

> **Hazlo siempre que dejes el computador solo**, sobre todo si es un
> computador compartido en la tienda. Si no cierras sesión, cualquiera
> que se siente puede modificar o borrar el inventario.

---

## 11. Preguntas frecuentes

**¿Puedo usarlo desde el celular?**
Sí. La pantalla se acomoda sola al tamaño del celular. Solo debe estar
conectado a la misma red que el computador donde está instalado.

**¿Puedo usarlo sin internet?**
Sí. El sistema funciona completo sin internet. Solo cambian dos cosas:
se ve sin colores ni estilos, y el precio en dólares sale aproximado.

**¿Dos personas pueden usarlo al mismo tiempo?**
Sí, si ambas están en la misma red. Los cambios de una se ven al
recargar la página de la otra.

**¿Puedo cambiar la contraseña?**
Todavía no desde el sistema. Por ahora hay que hacerlo desde
phpMyAdmin. Es una mejora pendiente.

**¿Puedo crear más usuarios?**
Igual que lo anterior: por ahora solo desde phpMyAdmin.

**¿Por qué me pide el precio sin puntos?**
Porque el sistema necesita el número limpio para poder hacer las
cuentas de las estadísticas. Los puntos se los pone él al mostrarlo.

**¿Se puede subir una foto desde el computador?**
Todavía no: por ahora se pega la dirección de una foto que ya esté en
internet. La subida de archivos es el reto que el equipo tiene
pendiente.

**¿Dónde quedan guardados los datos?**
En la base de datos MySQL del computador donde está instalado el
sistema. No se guardan en internet.

---

## 12. Qué hacer si algo sale mal

| Lo que ves | Qué pasó | Qué hacer |
|---|---|---|
| **"No se pudo conectar a la base de datos"** | MySQL está apagado | Abre XAMPP y pulsa Start en MySQL |
| Pantalla **completamente en blanco** | Error del programa | Recarga con `Ctrl + F5`. Si sigue, avisa al equipo |
| La página se ve **sin colores**, como texto plano | No hay internet (no cargó Bootstrap) | Conéctate a internet, o pídele al equipo que descargue Bootstrap |
| **"Esa prenda no existe o ya fue eliminada"** | Alguien la borró desde otro computador | Vuelve al inventario y recarga |
| **"Debes iniciar sesión para entrar"** | Pasó mucho tiempo sin usar el sistema | Vuelve a entrar con tu usuario |
| El precio en dólares sale **amarillo** | No se pudo consultar la tasa | No es un problema. El resto funciona normal |
| **Error 404 — No se encuentra** | La dirección está mal escrita | Verifica: `http://localhost/mvp-ventas-ropa/` |
| Aparece el **código PHP** en pantalla | Apache está apagado | Abre XAMPP y pulsa Start en Apache |

### Si nada de esto funciona

1. Cierra el navegador por completo y vuelve a abrirlo
2. Reinicia Apache y MySQL desde el panel de XAMPP
3. Reinicia el computador
4. Si el problema sigue, avisa al equipo de desarrollo y cuéntales
   **exactamente qué estabas haciendo** cuando falló — ese dato es el
   que más ayuda a encontrar el problema

---

*Manual elaborado por el equipo de Ventas de Ropa · Ficha 3168431*
*SENA · CIAS Regional Boyacá · 2026*
