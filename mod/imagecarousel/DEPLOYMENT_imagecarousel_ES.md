# Manual de despliegue a producción — Plugin `mod_imagecarousel` (Carrusel de imágenes)

Fecha: 24 octubre 2025

Este documento explica paso a paso cómo desplegar en producción los cambios realizados en el plugin `mod_imagecarousel` para añadir la funcionalidad de "Disponibilidad" (programación mediante fecha/hora), incluyendo la actualización de la base de datos, purga de cachés, verificación y plan de rollback.

Índice
- Resumen de los cambios
- Requisitos previos
- Preparación antes del despliegue (backups)
- Procedimiento de despliegue (paso a paso)
- Comandos de ejemplo (PowerShell / Windows)
- Verificaciones post-despliegue (QA checklist)
- Rollback (cómo revertir si algo falla)
- Notas de seguridad y consideraciones adicionales
- Siguientes mejoras recomendadas

---

## 1) Resumen de los cambios realizados
Se implementó la capacidad de programar la visibilidad del carrusel mediante dos campos de fecha/hora y la lógica que oculta el carrusel fuera del intervalo configurado. Archivos modificados:

- `mod/imagecarousel/mod_form.php`
  - Añadida sección "Disponibilidad" con dos campos `date_time_selector`:
    - `availablefrom` (fecha/hora desde)
    - `availableuntil` (fecha/hora hasta)

- `mod/imagecarousel/lib.php`
  - En `imagecarousel_add_instance()` y `imagecarousel_update_instance()` se normalizan/aseguran los campos `availablefrom` y `availableuntil` (por compatibilidad y valores por defecto).
  - En `imagecarousel_cm_info_view(cm_info $cm)` se añadió la comprobación temporal para ocultar el contenido del carrusel cuando la fecha actual está fuera del intervalo (a menos que el usuario esté en modo edición).

- `mod/imagecarousel/db/install.xml`
  - Añadidos campos en la tabla `imagecarousel` para instalaciones nuevas:
    - `availablefrom` INT(10) DEFAULT 0
    - `availableuntil` INT(10) DEFAULT 0

- `mod/imagecarousel/db/upgrade.php`
  - Añadido bloque de actualización que añade las columnas `availablefrom` y `availableuntil` en instalaciones existentes al subir la versión del plugin.

- `mod/imagecarousel/version.php`
  - Incrementada la versión del plugin a `2025101503` (para que Moodle ejecute el paso de `upgrade.php`).

- `mod/imagecarousel/lang/es/imagecarousel.php`
  - Añadidas cadenas de idioma en español:
    - `availability`, `availablefrom`, `availablefrom_help`, `availableuntil`, `availableuntil_help`.

- `mod/imagecarousel/lang/en/imagecarousel.php`
  - Añadidas cadenas de idioma en inglés equivalentes para evitar placeholders `[[availability]]` en sitios en inglés.

## 2) Requisitos previos
Antes de desplegar en producción asegúrate de lo siguiente:

- Acceso SSH o acceso a la máquina Windows / servidor web donde está alojado Moodle.
- Ruta al ejecutable `php.exe` del entorno del servidor (necesaria para ejecutar scripts CLI de Moodle). En WAMP suele estar en `C:\wamp64\bin\php\php<version>\php.exe`.
- Usuario con permisos para detener/poner en mantenimiento el sitio Moodle y para subir archivos (SFTP/FTP/rsync/archivo zip).
- Permisos para ejecutar los scripts CLI de Moodle y modificar la base de datos si es necesario.
- Copia de seguridad completa de la base de datos de Moodle y del directorio del plugin (`mod/imagecarousel`) antes del despliegue.

IMPORTANTE: hacer backup de la base de datos es obligatorio. Si la tabla o columnas se crean/modifican por `upgrade.php`, la única forma segura de revertir es restaurar la copia de seguridad de la base de datos.

## 3) Preparación antes del despliegue (Backups)
1. Backup de la base de datos (MySQL/MariaDB). Ejemplo (desde el servidor):

```powershell
# Windows PowerShell (ajusta usuario, contraseña y base de datos)
mysqldump -u root -p --databases nombre_base_de_datos_moodle > C:\backups\moodle_db_pre_imagecarousel_$(Get-Date -Format yyyyMMdd_HHmm).sql
```

2. Backup de la carpeta del plugin actual (si existe):

```powershell
# Comprime la carpeta actual del plugin
Compress-Archive -Path c:\wamp64\www\zajuna\mod\imagecarousel -DestinationPath C:\backups\imagecarousel_before_deploy_$(Get-Date -Format yyyyMMdd_HHmm).zip
```

3. (Opcional) Exporta una lista del esquema actual de la tabla `imagecarousel` para comparar con los cambios:

```sql
-- desde cliente mysql
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'nombre_base_de_datos_moodle'
  AND TABLE_NAME = 'imagecarousel';
```

Guarda la salida en caso de que necesites comparar.

## 4) Procedimiento de despliegue (paso a paso)
A continuación el flujo recomendado para un despliegue seguro y reproducible.

1) Poner Moodle en modo mantenimiento
- Web: Site administration -> Server -> Maintenance mode -> Activar.
- CLI (opcional):

```powershell
C:\ruta\a\php.exe c:\wamp64\www\zajuna\admin\cli\maintenance.php --enable
```

2) Subir los archivos del plugin
- Reemplazar la carpeta `mod/imagecarousel` en el servidor con la versión modificada (puedes usar SFTP, rsync o subir un ZIP y descomprimirlo).
- Asegúrate de que los permisos de archivos/propietario sean correctos (mismo usuario que usa el servidor web).

3) Ejecutar la actualización de Moodle (upgrade) para que ejecute `db/upgrade.php` y añada las columnas en la BD
- CLI (recomendado):

```powershell
# Ajusta la ruta a php.exe si corresponde
C:\ruta\a\php.exe c:\wamp64\www\zajuna\admin\cli\upgrade.php
```

- Si prefieres hacerlo desde la web, visita: Site administration -> Notifications. Moodle detectará el nuevo plugin y te pedirá ejecutar la actualización.

4) Purga de cachés (necesario para que Moodle cargue nuevas cadenas y plantillas)
- CLI:

```powershell
C:\ruta\a\php.exe c:\wamp64\www\zajuna\admin\cli\purge_caches.php
```

- O desde web: Site administration -> Development -> Purge caches.

5) Verificar que las columnas fueron creadas (opcional, SQL)

```sql
SELECT COLUMN_NAME FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'nombre_base_de_datos_moodle' AND TABLE_NAME = 'imagecarousel'
  AND COLUMN_NAME IN ('availablefrom', 'availableuntil');
```

6) Verificación básica de la UI (realizar pruebas manuales)
- Accede a un curso -> Añadir/Editar instancia del plugin "Carrusel de imágenes" (modedit.php).
- Verifica que en la sección "Disponibilidad" aparecen los selectores de fecha/hora con las cadenas en el idioma activo (p. ej. "Fecha y hora de disponibilidad" / "Available from").
- Configura `availablefrom` en el futuro y guarda. Sal del modo edición y accede como estudiante para comprobar que el carrusel no se muestra antes de la fecha.
- Configura `availableuntil` en el pasado y guarda para comprobar que deja de mostrarse.

7) Desactivar el modo mantenimiento

```powershell
C:\ruta\a\php.exe c:\wamp64\www\zajuna\admin\cli\maintenance.php --disable
```

8) Notificar a los responsables (profesores/administradores) que la funcionalidad ya está disponible.

## 5) Comandos de ejemplo (PowerShell / Windows)
Ajusta la ruta a `php.exe` según tu instalación WAMP/XAMPP. Ejemplos usando una ruta típica de WAMP (ajusta `php7.4.0` a tu versión real):

```powershell
# 1) Poner en mantenimiento
C:\wamp64\bin\php\php7.4.0\php.exe c:\wamp64\www\zajuna\admin\cli\maintenance.php --enable

# 2) Ejecutar upgrade de Moodle (actualiza DB si es necesario)
C:\wamp64\bin\php\php7.4.0\php.exe c:\wamp64\www\zajuna\admin\cli\upgrade.php

# 3) Purga caches
C:\wamp64\bin\php\php7.4.0\php.exe c:\wamp64\www\zajuna\admin\cli\purge_caches.php

# 4) Deshabilitar mantenimiento
C:\wamp64\bin\php\php7.4.0\php.exe c:\wamp64\www\zajuna\admin\cli\maintenance.php --disable
```

Si no conoces la ruta a `php.exe` en WAMP, búscala con: `Get-Command php` o revisa `C:\wamp64\bin\php\`.

## 6) Verificaciones post-despliegue (QA checklist)
Realiza estas comprobaciones antes de cerrar el despliegue:

- [ ] La actualización (`admin/cli/upgrade.php`) finalizó sin errores.
- [ ] La tabla `imagecarousel` contiene las columnas `availablefrom` y `availableuntil` (o `install.xml` fue aplicado correctamente en nuevas instalaciones).
- [ ] En la página de edición del plugin se ven las etiquetas y selectores de fecha/hora correctamente (en el idioma del sitio).
- [ ] El carrusel se oculta para los estudiantes cuando la fecha actual está fuera del intervalo, pero sigue visible en modo edición.
- [ ] No aparecen marcadores tipo `[[availability]]` u otros placeholders; si aparecen, purga cachés y verifica que las cadenas de idioma existen en `lang/en` y `lang/es`.
- [ ] Comprobar logs de errores de PHP/Apache después del upgrade para detectar warnings o errores.
- [ ] Probar con diferentes roles (profesor, estudiante, administrador) para confirmar comportamiento de visibilidad.

## 7) Rollback (revertir si algo falla)
Si se produce un fallo grave que no se puede solucionar rápidamente, sigue este plan de reversión:

1. Poner el sitio en modo mantenimiento (si no está ya).
2. Restaurar la copia de seguridad de la base de datos realizada antes del despliegue.
   - Ejemplo (MySQL):

```powershell
mysql -u root -p nombre_base_de_datos_moodle < C:\backups\moodle_db_pre_imagecarousel_YYYYMMDD_HHMM.sql
```

3. Restaurar la carpeta física del plugin (`mod/imagecarousel`) con la copia ZIP que guardaste:

```powershell
# Descomprimir backup que hiciste antes
Expand-Archive -Path C:\backups\imagecarousel_before_deploy_YYYYMMDD_HHMM.zip -DestinationPath c:\wamp64\www\zajuna\mod\ -Force
```

4. Purga caches:

```powershell
C:\ruta\a\php.exe c:\wamp64\www\zajuna\admin\cli\purge_caches.php
```

5. Desactivar modo mantenimiento.
6. Verifica que el sitio y el módulo vuelven a su estado anterior.

Notas sobre rollback: restaurar la BD y los archivos al estado anterior es la forma más fiable. No intentes revertir `upgrade.php` manualmente (borrar columnas) salvo que sepas exactamente lo que estás haciendo.

## 8) Notas de seguridad y consideraciones
- Asegura permisos correctos en la carpeta del plugin. Los ficheros deben ser legibles por el usuario del servidor web.
- Las cadenas de idioma han sido añadidas a `lang/en` y `lang/es`. Si tu sitio usa otro idioma, añade las claves en el fichero de idioma correspondiente para evitar placeholders.
- La comprobación de visibilidad se basa en la marca de tiempo UNIX almacenada en `availablefrom` y `availableuntil`. El comportamiento actual: el plugin está oculto a usuarios que no están en modo edición si la hora actual está fuera del rango. Si necesitas más reglas (p. ej. visibilidad por rol, grupo, etc.) considera integrar la Availability API (restricciones de acceso) de Moodle.
- Revisa `mod_imagecarousel_pluginfile()` en `lib.php` y asegurate de que las áreas de archivos y permisos no se hayan visto afectadas por los cambios.

## 9) Pruebas recomendadas y casos límite
- Crear un carrusel con `availablefrom` 1 hora en el futuro; comprobar que los usuarios sin edición no lo ven.
- Crear un carrusel con `availableuntil` 1 hora en el pasado; comprobar que no se ve.
- Comprobar comportamiento con timezone distintas: verificar la hora del servidor y la zona horaria de Moodle (Site administration -> Server -> Location -> Timezone) para evitar desfases inesperados.
- Comprobar con editores que no sean Administrador (profesor con permisos de edición) que efectivamente pueden ver/editar el carrusel aun si está fuera del rango.
- Comprobar que el upgrade no genera duplicados ni errores en la tabla `imagecarousel`.

## 10) Siguientes mejoras recomendadas
- Integrar la funcionalidad con la Availability API (Restricciones de acceso) de Moodle para disponer de reglas más potentes y la UI estándar.
- Añadir tests automatizados (unit/integration) para validar la lógica de visibilidad y las migraciones de la base de datos.
- Añadir logs que registren cuándo un carrusel no fue mostrado por estar fuera de rango (útil para auditoría/debugging).

---

Si quieres, adapto este manual para incluir comandos concretos según tu servidor (por ejemplo la ruta real de `php.exe` en tu WAMP) y añado un pequeño script PowerShell que automatiza los pasos (poner mantenimiento, upgrade y purge). ¿Deseas que genere ese script también? 
