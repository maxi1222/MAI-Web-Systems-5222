# MAI Web Systems

### Sistema web para la gestión de pedidos de una panadería

Proyecto académico desarrollado por el equipo **MAI Web Systems** para 3.º MD de Tecnologías de la Información (UTU, 2026).

## Descripción

MAI Web Systems es una empresa ficticia dedicada al desarrollo de soluciones de software personalizadas. En este proyecto se desarrolla un sistema web para la panadería **La Ideal**, con el objetivo de digitalizar la recepción y organización de pedidos, reemplazando los registros manuales y centralizando la información.

Los clientes pueden consultar el catálogo y realizar encargos para retirar en el local. El personal de la panadería dispone de un panel interno para organizar los pedidos, actualizar sus estados y administrar los productos.

## Funcionalidades principales

### Módulo público: clientes

- Consultar el catálogo de productos.
- Buscar productos por nombre o descripción y filtrarlos por categoría.
- Consultar la disponibilidad de productos según el día de elaboración elegido.
- Ver si un producto está agotado o si aparece como destacado.
- Agregar productos al carrito y modificar las cantidades.
- Confirmar un pedido indicando nombre, teléfono, fecha y hora de retiro.
- Consultar la confirmación y el resumen del pedido.
- Dar «Me gusta» a los productos y consultar sus contadores.

El sistema valida que los pedidos se realicen con al menos **24 horas de anticipación** y controla un cupo diario de pedidos, configurable desde `config/conexion.php`.

### Panel interno: empleados y administradores

- Iniciar sesión mediante una cuenta registrada en el sistema.
- Consultar la agenda de pedidos en vistas diaria, semanal o mensual.
- Visualizar los pedidos en un calendario mensual.
- Buscar pedidos por fechas o por nombre del cliente.
- Actualizar el estado de un pedido: Pendiente, En preparación, Listo o Entregado.
- Consultar estadísticas de ventas.

Los administradores también pueden agregar y editar productos, modificar su disponibilidad, configurar los días de elaboración y seleccionar los productos destacados de la portada. Las operaciones disponibles dependen del rol de la cuenta.

## Tecnologías utilizadas

| Área | Tecnología |
|---|---|
| Interfaz | HTML5, CSS3 y JavaScript |
| Lógica del servidor | PHP |
| Base de datos principal | MySQL / MariaDB |
| Datos de «Me gusta» | Firebase Realtime Database (estructura JSON) |
| Entorno de desarrollo local | XAMPP (Apache, PHP y MySQL/MariaDB) |

La base de datos relacional almacena los usuarios, productos, pedidos y detalles de los pedidos. Firebase Realtime Database se utiliza para los contadores de «Me gusta» de los productos.

## Requisitos para ejecutar el proyecto

- XAMPP u otro entorno local que incluya Apache, PHP y MySQL/MariaDB.
- PHP con la extensión `mysqli` habilitada.
- Un navegador web actualizado.
- Conexión a Internet para consultar y actualizar los contadores de «Me gusta» alojados en Firebase.

## Instalación y configuración local

Los siguientes pasos describen la ejecución con XAMPP.

### 1. Descargar el repositorio

Cloná el repositorio dentro de la carpeta `htdocs` de XAMPP:

```bash
cd /opt/lampp/htdocs
git clone https://github.com/maxi1222/MAI-Web-Systems-5222.git
```

En Windows, la carpeta habitual de destino es `C:\xampp\htdocs`.

También se puede descargar el repositorio como ZIP y extraer la carpeta del proyecto dentro de `htdocs`.

### 2. Iniciar los servicios

Abrí el administrador de XAMPP e iniciá **Apache** y **MySQL**.

### 3. Importar la base de datos

1. Abrí [phpMyAdmin](http://localhost/phpmyadmin/).
2. Entrá en la sección **Importar**.
3. Seleccioná el archivo `Base_de_datos/MAI_Panaderia.sql`.
4. Ejecutá la importación.

El script crea la base de datos `MAI_Panaderia` y sus tablas: `usuarios`, `productos`, `pedidos` y `detalle_pedidos`.

> **Importante:** el archivo SQL contiene instrucciones `DROP TABLE IF EXISTS`. Al importarlo nuevamente se eliminan y recrean las tablas existentes, por lo que se pueden perder los datos guardados. Utilizalo para una instalación inicial o de desarrollo, y realizá una copia de seguridad antes de volver a importarlo.

### 4. Revisar la conexión a MySQL

La conexión se configura en `config/conexion.php`. Los valores incluidos corresponden a una instalación habitual de XAMPP:

```php
$servidor = "localhost";
$usuario_bd = "root";
$contrasena_bd = "";
$base_datos = "MAI_Panaderia";
```

Si tu instalación utiliza otro usuario, contraseña o servidor, modificá esos valores. En ese mismo archivo también se encuentran configuraciones como el cupo diario de pedidos y el teléfono del local.

### 5. Abrir el sistema

Con Apache y MySQL en funcionamiento, ingresá desde el navegador a:

```text
http://localhost/MAI-Web-Systems-5222/
```

Si la carpeta del proyecto tiene otro nombre, reemplazá `MAI-Web-Systems-5222` por el nombre correspondiente.

## Firebase Realtime Database

La integración con Firebase se utiliza para almacenar los contadores de «Me gusta» de los productos. La lógica de esta funcionalidad se encuentra en `assets/js/firebase_likes.js` y la configuración de Firebase está dentro de la carpeta `firebase/`:

- `firebase/.firebaserc`: identifica el proyecto de Firebase asociado.
- `firebase/firebase.json`: indica el archivo de reglas de Realtime Database.
- `firebase/database.rules.json`: contiene las reglas de acceso y validación.

El código JavaScript consulta Firebase mediante solicitudes HTTP y guarda en el navegador, mediante `localStorage`, los productos a los que el visitante ya les dio «Me gusta».

Para publicar cambios en las reglas se necesita Firebase CLI, iniciar sesión con una cuenta que tenga acceso al proyecto y ejecutar desde la carpeta `firebase/`:

```bash
firebase login
firebase deploy --only database
```

> **Consideración de seguridad:** las reglas incluidas permiten lectura y escritura públicas dentro de `likes_productos`. La marca de «Me gusta» guardada en `localStorage` evita repetir la acción desde ese mismo navegador, pero no identifica de forma segura a una persona. Esta configuración debe revisarse antes de utilizar el sistema en un entorno de producción.

## Estructura del proyecto

La siguiente estructura muestra las principales carpetas y archivos del repositorio. Se incluyen por separado las acciones del sitio público y las acciones del panel interno.

```text
MAI-Web-Systems-5222/
├── Base_de_datos/
│   └── MAI_Panaderia.sql
├── acciones/                         # Acciones del módulo público
│   ├── carrito_agregar.php
│   ├── carrito_quitar.php
│   └── pedido_guardar.php
├── assets/
│   ├── css/
│   │   └── estilos.css
│   ├── img/
│   │   └── productos/
│   │       ├── default_panaderia.svg
│   │       ├── imagenes.txt
│   │       └── 1793195543_IMG_20251210_142718.jpg
│   └── js/
│       ├── main.js
│       └── firebase_likes.js
├── config/
│   └── conexion.php
├── firebase/
│   ├── .firebaserc
│   ├── database.rules.json
│   ├── firebase.json
│   └── README.md
├── includes/
│   ├── auth.php
│   ├── footer.php
│   ├── funciones.php
│   ├── header_panel.php
│   └── header_publico.php
├── panel/
│   ├── acciones/                     # Procesos del panel interno
│   │   ├── pedido_estado.php         # Actualiza el estado de un pedido
│   │   ├── producto_estado.php       # Cambia disponibilidad o destacado
│   │   └── producto_guardar.php      # Registra o actualiza productos
│   ├── agenda.php
│   ├── calendario.php
│   ├── estadisticas.php
│   ├── login.php
│   ├── logout.php
│   ├── pedidos_buscar.php
│   ├── producto_editar.php
│   └── productos.php
├── carrito.php
├── catalogo.php
├── confirmacion.php
├── index.php
└── README.md
```

### Acciones del panel interno (`panel/acciones/`)

- **`pedido_estado.php`:** recibe la solicitud para cambiar el estado de un pedido, valida que el nuevo estado sea permitido y actualiza el registro en MySQL.
- **`producto_guardar.php`:** valida y registra un producto nuevo o guarda los cambios de un producto existente, incluyendo sus datos, imagen, disponibilidad, si está destacado y los días de elaboración.
- **`producto_estado.php`:** permite modificar la disponibilidad de un producto (disponible o agotado) y su condición de destacado, aplicando la restricción de que solo el administrador pueda modificar esta última.

## Alcance y consideraciones

- Los pedidos son para retirar en el local.
- El sistema no incluye procesamiento de pagos con tarjeta en línea ni gestión de envíos a domicilio.
- La modificación o cancelación de un pedido confirmado se deriva a la atención telefónica del local; no se realiza directamente desde la web.
- Los valores de conexión, el cupo diario y el teléfono del local se pueden ajustar en `config/conexion.php`.

## Mockup

https://www.figma.com/design/V0RoXnnm6N1wQwlreignUO/mokup?node-id=0-1&t=Lie1uCFY7FUIOsEL-1

## Equipo de desarrollo

- **Iñaki Barreda**
- **Maximiliano Castellanos**
- **Alejandro García**

**MAI Web Systems — Proyecto UTU 2026, 3.º MD Tecnologías de la Información.**
