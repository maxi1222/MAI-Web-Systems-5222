# Configuración de Firebase - Panadería La Ideal (MAI Web Systems)

Esta carpeta contiene la configuración y reglas de seguridad para la integración de Firebase Realtime Database (Likes de productos):

- **`.firebaserc`**: Vincula el proyecto por defecto de Firebase (`mai-panaderia`).
- **`firebase.json`**: Archivo de manifiesto que define la configuración de la base de datos en tiempo real.
- **`database.rules.json`**: Reglas de seguridad JSON para Realtime Database:
  - Lectura pública en el nodo `/likes_productos`.
  - Validación de campos requeridos: `id_producto` (> 0) y `total` (>= 0).
  - Bloqueo de lectura y escritura en la raíz de la base de datos.
