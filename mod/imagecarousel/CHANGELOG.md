# Changelog - Módulo ImageCarousel

## [v0.3.0] - 2025-04-03

### 🚀 Nuevas Funcionalidades
- **Soporte completo para WebP**: Agregado soporte nativo para imágenes WebP con detección automática de formato
- **Imágenes responsivas**: Implementado sistema de imágenes específicas para dispositivos móviles y de escritorio
- **Almacenamiento Base64**: Migración completa del sistema de archivos a almacenamiento Base64 para mejor portabilidad
- **Sistema de opacidad avanzado**: Control granular de opacidad para colores de texto y fondos (0-100%)

### 🔧 Mejoras Técnicas
- **Migración de base de datos**: Actualización completa de la estructura de tablas para soportar nuevas funcionalidades
- **Gestión de archivos mejorada**: Eliminación de dependencias del sistema de archivos de Moodle
- **Optimización de rendimiento**: Mejoras en la carga y renderizado de imágenes
- **Compatibilidad con Moodle 4.1+**: Verificaciones de permisos y seguridad actualizadas

### 🎨 Mejoras de UI/UX
- **Selector de colores integrado**: Interfaz visual para selección de colores con previsualización en tiempo real
- **Controles de opacidad**: Sliders interactivos para ajustar transparencias
- **Posicionamiento personalizado**: Control granular de posición de texto (top, right, bottom, left)
- **Estilos de texto avanzados**: Soporte para negrita, cursiva y subrayado

### 🐛 Correcciones de Bugs
- **Compatibilidad móvil**: Corregidos problemas de detección de dispositivos móviles
- **Gestión de errores**: Mejorado el manejo de errores en la carga de imágenes
- **Validación de datos**: Validación mejorada de formularios y datos de entrada
- **Limpieza de caché**: Implementada limpieza automática de cachés del sistema

---

## [v0.2.0] - 2025-04-02

### 🚀 Nuevas Funcionalidades
- **Soporte para dispositivos móviles**: Implementación inicial de detección de dispositivos móviles
- **Sistema de permisos**: Control de acceso basado en capacidades de Moodle
- **Gestión de imágenes mejorada**: Interfaz para administrar múltiples imágenes por carrusel

### 🔧 Mejoras Técnicas
- **Estructura de base de datos**: Agregado campo `is_mobile` para identificar imágenes específicas de dispositivo
- **Sistema de archivos**: Mejoras en la gestión de archivos subidos vs URLs externas
- **Validación de seguridad**: Verificaciones de permisos en páginas de administración

---

## [v0.1.0] - 2025-04-01

### 🚀 Lanzamiento Inicial
- **Carrusel básico**: Funcionalidad fundamental de carrusel de imágenes
- **Gestión de texto**: Sistema básico de texto superpuesto con opciones de personalización
- **Sistema de archivos**: Soporte para archivos subidos y URLs externas
- **Estructura de base de datos**: Tablas iniciales para carruseles e imágenes

### 🔧 Características Técnicas
- **Integración con Moodle**: Módulo completamente integrado con el sistema de Moodle
- **Sistema de permisos básico**: Control de acceso para profesores y administradores
- **Gestión de archivos**: Integración con el sistema de archivos de Moodle
- **Formularios estándar**: Uso de las librerías de formularios estándar de Moodle

---

## Información del Desarrollador

**Desarrollado por:** Zajuna Team - Andres Eduardo Brochero  
**Licencia:** GNU GPL v3 o posterior  
**Compatibilidad:** Moodle 4.1+ (requiere versión 2022112800)  
**Estado:** Alpha (en desarrollo activo)

---

## Notas de Instalación

### Requisitos del Sistema
- Moodle 4.1 o superior
- PHP 7.4+
- Soporte para Base64 encoding
- Permisos de administrador para activar soporte WebP

### Activación de Soporte WebP
Para habilitar el soporte completo de WebP, ejecutar como administrador:
```
/mod/imagecarousel/webp-support.php
```

### Migración de Versiones Anteriores
El sistema incluye scripts de migración automática que:
1. Migran datos existentes a la nueva estructura Base64
2. Actualizan la base de datos automáticamente
3. Preservan todas las configuraciones existentes

---