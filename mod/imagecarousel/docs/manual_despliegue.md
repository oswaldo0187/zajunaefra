# Manual de Despliegue del Plugin IMAGECAROUSEL

## 1. Requisitos Previos

- Moodle instalado y configurado
- Acceso al servidor con permisos de administrador
- Acceso a la base de datos de Moodle
- PHP 7.4 o superior
- MySQL/MariaDB o PostgreSQL

## 2. Preparación del Plugin

### 2.1 Estructura del Plugin
El plugin IMAGECAROUSEL tiene la siguiente estructura:
```
mod/imagecarousel/
├── amd/           # Archivos JavaScript AMD
├── classes/       # Clases PHP
├── db/           # Archivos de base de datos
├── lang/         # Archivos de idioma
├── pix/          # Imágenes y recursos gráficos
├── templates/    # Plantillas Mustache
├── version.php   # Versión del plugin
├── lib.php       # Funciones principales
├── view.php      # Vista principal
├── edit.php      # Edición de carruseles
├── manage.php    # Gestión de imágenes
└── styles.css    # Estilos CSS
```

## 3. Proceso de Instalación

### 3.1 Copia de Archivos
```bash
# Copiar el directorio del plugin al directorio mod/ de Moodle
cp -r imagecarousel /ruta/a/moodle/mod/
```

### 3.2 Permisos de Archivos
```bash
# Asegurar que los archivos tengan los permisos correctos
chmod -R 755 /ruta/a/moodle/mod/imagecarousel
chown -R www-data:www-data /ruta/a/moodle/mod/imagecarousel
```

### 3.3 Instalación en Moodle
1. Acceder al panel de administración de Moodle
2. Navegar a: Administración del sitio > Notificaciones
3. El sistema detectará automáticamente el nuevo plugin
4. Seguir las instrucciones para completar la instalación

## 4. Configuración Post-Instalación

### 4.1 Permisos de Usuario
1. Navegar a: Administración del sitio > Usuarios > Permisos > Definir roles
2. Configurar los siguientes permisos para los roles necesarios:
   - `mod/imagecarousel:addinstance`
   - `mod/imagecarousel:view`
   - `mod/imagecarousel:manage`

### 4.2 Configuración del Módulo
1. Navegar a: Administración del sitio > Plugins > Módulos de actividad > Image Carousel
2. Configurar las opciones generales:
   - Tamaño máximo de imágenes
   - Formatos de imagen permitidos
   - Configuración de caché

## 5. Verificación de la Instalación

### 5.1 Pruebas de Funcionalidad
1. Crear un nuevo curso
2. Agregar una actividad Image Carousel
3. Verificar que se puedan:
   - Subir imágenes
   - Configurar el carrusel
   - Ver el carrusel funcionando

### 5.2 Verificación de Base de Datos
```sql
-- Verificar que las tablas se hayan creado correctamente
SHOW TABLES LIKE 'mdl_imagecarousel%';
```

## 6. Mantenimiento

### 6.1 Actualizaciones
1. Seguir el mismo proceso de instalación para actualizaciones
2. El sistema detectará automáticamente la nueva versión
3. Seguir las instrucciones de actualización

### 6.2 Respaldo
1. Incluir el directorio `mod/imagecarousel` en el respaldo regular de Moodle
2. Incluir las tablas de la base de datos en el respaldo

## 7. Solución de Problemas Comunes

### 7.1 Problemas de Permisos
- Verificar los permisos de archivos y directorios
- Verificar los permisos de usuario en Moodle

### 7.2 Problemas de Base de Datos
- Verificar que las tablas se hayan creado correctamente
- Verificar los logs de error de Moodle

### 7.3 Problemas de Visualización
- Verificar la configuración de caché
- Limpiar la caché del navegador
- Verificar la configuración de JavaScript

## 8. Documentación Adicional

Para más detalles sobre la configuración específica, consultar:
- `version.php` para requisitos de versión
- `lib.php` para funciones principales
- `lang/` para traducciones disponibles

## 9. Soporte

- Para problemas técnicos, contactar al equipo de soporte
- Mantener un registro de cambios y versiones
- Documentar cualquier personalización realizada 