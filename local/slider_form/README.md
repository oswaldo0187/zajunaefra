## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/slider

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2024 Your Name <you@example.com>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.

# Manual de Desarrollador - Plugin Slider para Moodle --------

## Descripción
Este plugin permite la gestión y visualización de un slider de imágenes en Moodle, con soporte para diferentes dispositivos (escritorio y móvil). El plugin está compuesto por dos módulos principales:

1. `local_slider`: Módulo para la visualización del slider
2. `local_slider_form`: Módulo para la gestión administrativa del slider

## Requisitos del Sistema
- Moodle 4.0 o superior
- PHP 7.0 o superior
- Permisos de administrador para la instalación

## Instalación

### Instalación mediante archivo ZIP
1. Inicie sesión en su sitio Moodle como administrador
2. Vaya a Administración del sitio > Plugins > Instalar plugins
3. Suba el archivo ZIP del plugin
4. Verifique el reporte de validación del plugin
5. Complete la instalación

### Instalación Manual
1. Copie el contenido del directorio a:
   ```
   {su/directorio/moodle}/local/slider
   {su/directorio/moodle}/local/slider_form
   ```
2. Inicie sesión como administrador y vaya a Administración del sitio > Notificaciones
3. Complete la instalación

Alternativamente, puede ejecutar en la terminal de comandos:
```bash
php admin/cli/upgrade.php
```

**_Nota_**: Esta instalacion de pluggin se puede encontrar en el manual de instalacion "Manual Plugin Slider"

## Estructura del Plugin

### Módulo Slider (local_slider)
```
local/slider/
├── lib/
│   └── showSlider.php    # Funciones de visualización
├── js/
│   └── screenScript.js   # Scripts del cliente
├── lang/
│   └── es/              # Traducciones
└── version.php          # Versión del plugin
```

### Módulo Formulario (local_slider_form)
```
local/slider_form/
├── classes/
│   ├── forms/           # Clases de formularios
│   ├── modal/          # Clases de modales
│   ├── tabs/           # Clases de pestañas
│   └── table/          # Clases de tablas
├── lib/
│   ├── fieldsValidations.php  # Validaciones de campos
│   ├── usersValidations.php   # Validaciones de usuarios
│   └── utilsFunctions.php     # Funciones utilitarias
├── css/
│   └── formUpdate.css   # Estilos
├── js/
│   └── script.js        # Scripts
└── version.php          # Versión del plugin
```

## Funcionalidades Principales

### Gestión de Imágenes
- Subida de imágenes para escritorio y móvil
- Validación de formatos de imagen
- Prevención de duplicados
- Ordenamiento personalizado

### Seguridad
- Validación de sesión de usuario
- Verificación de roles y permisos
- Protección CSRF
- Validación de datos de entrada

### Caché
- Sistema de caché para optimizar el rendimiento
- Actualización automática al modificar registros activos

## API y Funciones Principales

### Visualización del Slider
```php
// Permite cargar activos del slider
loadSwiperAssets();

// Obtener imágenes del slider
getImages();

// Mostrar el slider en el front
slider();
```

### Gestión de Registros
```php
// Insertar nuevo registro en la base de datos segun las imagenes a subir
insertRecord();

// Actualizar registro existente
updateRecord();

// Eliminar registro
deleteRecord();

// Actualizar orden de imagenes presentadas
updateOrder();
```

## Validaciones

### Validaciones de Usuario
- `checkSession()`: Verifica la sesión activa
- `checkUserRole()`: Verifica permisos de administrador
- `checkCsrfToken()`: Valida token CSRF

### Validaciones de Campos
- `checkImagesNotBeTheSame()`: Evita imágenes duplicadas
- `checkRequiredImages()`: Verifica imágenes requeridas
- `checkImagesContent()`: Valida contenido de imágenes

## Base de Datos

### Tabla: local_slider
- `id`: Identificador único
- `name`: Nombre del registro
- `desktop_image`: Imagen para escritorio
- `mobile_image`: Imagen para móvil
- `state`: Estado (1 = activo, 0 = inactivo)
- `order_display`: Orden de visualización
- `created_by`: ID del creador
- `updated_by`: ID del actualizador
- `created_at`: Fecha de creación
- `updated_at`: Fecha de actualización

## Desarrollo

### Mantenimiento
- Actualizar la versión en `version.php` al realizar cambios
- Mantener las traducciones actualizadas
- Seguir las guías de codificación de Moodle
- Mantener el repositorio actualizado y validar cambios a traves de PR con supervisor

## Licencia
Este plugin está licenciado bajo la GNU General Public License v3 o posterior.

## Soporte
Para reportar problemas o solicitar nuevas características, por favor contacte al administrador del sistema - dsalcedot@sena.edu.co.
