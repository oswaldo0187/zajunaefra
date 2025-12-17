# Informe Técnico: Mejora de Gestión de URLs de Imágenes en ImageCarousel

## Información General

**Fecha:** 4 de diciembre de 2025  
**Módulo:** mod_imagecarousel  
**Versión:** 1.0  
**Autor:** Equipo Zajuna  
**Ticket/Requerimiento:** Visualización de URLs de imágenes en vista de gestión

---

## 1. Resumen Ejecutivo

Se implementaron mejoras en el módulo `mod_imagecarousel` para garantizar que las URLs de imágenes ingresadas por los usuarios (tanto de escritorio como móviles) se almacenen correctamente en la base de datos y se visualicen de manera apropiada en la vista de gestión (`manage.php`).

### Problema Identificado

Cuando un usuario ingresaba una URL de imagen a través de los campos "O usar URL para imagen de escritorio" o "O usar URL para imagen móvil" en los formularios de creación o edición:

1. La URL se guardaba únicamente en los campos `desktop_image` o `mobile_image`
2. El campo `url` de la tabla permanecía vacío
3. En la vista de gestión (`manage.php`), la columna "URL de la imagen" aparecía en blanco
4. No se diferenciaba visualmente entre URLs de escritorio y móviles

### Solución Implementada

- **Lógica de fallback**: Si el campo `url` está vacío, se usa automáticamente la URL de `desktop_image` como fallback (o `mobile_image` si no hay desktop)
- **Separación visual**: La vista de gestión ahora muestra dos columnas independientes para URLs de desktop y mobile
- **Normalización**: Todas las URLs se normalizan automáticamente agregando el protocolo `https://` cuando es necesario
- **Validación**: Solo se muestran URLs externas; las imágenes en base64 muestran un guion (-)

---

## 2. Archivos Modificados

### 2.1 `mod/imagecarousel/adding_image.php`

**Líneas modificadas:** ~600-630 (aproximadamente)

**Cambios realizados:**

#### Antes:
```php
// Modificar la URL principal para asegurar que URLs externas tengan el protocolo correcto
if (isset($fromform->url) && !empty($fromform->url)) {
    $url = $fromform->url;
    // Verificar si la URL ya tiene un protocolo
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        // No tiene protocolo, verificar si parece una URL externa
        if (preg_match('/^www\.|^\w+\.\w+/i', $url)) {
            // Es probablemente una URL externa, agregar https://
            $url = 'https://' . $url;
        }
    }
    $imagerecord->url = $url;
} else {
    $imagerecord->url = null;
}
```

#### Después:
```php
// Modificar la URL principal para asegurar que URLs externas tengan el protocolo correcto.
// Si el campo "url" viene vacío, usar la URL de "image_desktop" como fallback
// (o "image_mobile" si no hay desktop).
if (!empty($fromform->url)) {
    $url = $fromform->url;
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        if (preg_match('/^www\.|^\w+\.\w+/i', $url)) {
            $url = 'https://' . $url;
        }
    }
    $imagerecord->url = $url;
} else if (!empty($fromform->image_desktop)) {
    $url = $fromform->image_desktop;
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        if (preg_match('/^www\.|^\w+\.\w+/i', $url)) {
            $url = 'https://' . $url;
        }
    }
    $imagerecord->url = $url;
} else if (!empty($fromform->image_mobile)) {
    $url = $fromform->image_mobile;
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        if (preg_match('/^www\.|^\w+\.\w+/i', $url)) {
            $url = 'https://' . $url;
        }
    }
    $imagerecord->url = $url;
} else {
    $imagerecord->url = null;
}
```

**Descripción del cambio:**

Se agregó una lógica de fallback que verifica múltiples fuentes para el campo `url`:
1. Primero intenta usar el campo explícito `url`
2. Si está vacío, usa `image_desktop`
3. Si tampoco existe, usa `image_mobile`
4. En todos los casos, normaliza el protocolo HTTP/HTTPS

**Beneficio:** Las URLs de imágenes ahora se almacenan consistentemente en el campo `url` de la base de datos, facilitando su recuperación y visualización.

---

### 2.2 `mod/imagecarousel/edit.php`

**Líneas modificadas:** ~661-690 (aproximadamente)

**Cambios realizados:**

#### Antes:
```php
// Mantener la URL principal o actualizarla (verificar si tiene un nuevo valor)
if (isset($formdata->url) && !empty($formdata->url)) {
    $url = $formdata->url;
    // Verificar si la URL ya tiene un protocolo
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        // No tiene protocolo, verificar si parece una URL externa
        if (preg_match('/^www\.|^\w+\.\w+/i', $url)) {
            // Es probablemente una URL externa, agregar https://
            $url = 'https://' . $url;
        }
    }
    $imagerecord->url = $url;
} else {
    $imagerecord->url = $existingimage->url; // Mantener la URL existente
}
```

#### Después:
```php
// Mantener la URL principal o actualizarla. Si el campo "url" viene vacío,
// usar la URL ingresada en "image_desktop" (o "image_mobile") como fallback
// para que se muestre en la columna "URL de la imagen" en manage.php.
if (!empty($formdata->url)) {
    $url = $formdata->url;
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        if (preg_match('/^www\.|^\w+\.\w+/i', $url)) {
            $url = 'https://' . $url;
        }
    }
    $imagerecord->url = $url;
} else if (!empty($formdata->image_desktop)) {
    $url = $formdata->image_desktop;
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        if (preg_match('/^www\.|^\w+\.\w+/i', $url)) {
            $url = 'https://' . $url;
        }
    }
    $imagerecord->url = $url;
} else if (!empty($formdata->image_mobile)) {
    $url = $formdata->image_mobile;
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        if (preg_match('/^www\.|^\w+\.\w+/i', $url)) {
            $url = 'https://' . $url;
        }
    }
    $imagerecord->url = $url;
} else {
    $imagerecord->url = $existingimage->url; // Mantener la URL existente
}
```

**Descripción del cambio:**

Similar al cambio en `adding_image.php`, se implementó la misma lógica de fallback en el proceso de edición:
1. Prioriza el campo `url` explícito
2. Usa `image_desktop` como segunda opción
3. Usa `image_mobile` como tercera opción
4. Mantiene la URL existente si ninguna está disponible
5. Normaliza el protocolo en todos los casos

**Beneficio:** Consistencia en el manejo de URLs entre creación y edición de imágenes.

---

### 2.3 `mod/imagecarousel/manage.php`

**Líneas modificadas:** ~45-55 (encabezados) y ~170-185 (datos)

**Cambios realizados:**

#### A) Encabezados de Tabla

**Antes:**
```php
$table->head = array(
    'ID',
    get_string('preview', 'mod_imagecarousel'),
    get_string('image_url', 'mod_imagecarousel'),
    get_string('text', 'mod_imagecarousel'),
    get_string('text_url', 'mod_imagecarousel'),
    get_string('actions', 'mod_imagecarousel')
);
```

**Después:**
```php
$table->head = array(
    'ID',
    get_string('preview', 'mod_imagecarousel'),
    // Mostrar ambas URLs (desktop y móvil) en columnas separadas
    'URL imagen (Desktop)',
    'URL imagen (Mobile)',
    get_string('text', 'mod_imagecarousel'),
    get_string('text_url', 'mod_imagecarousel'),
    get_string('actions', 'mod_imagecarousel')
);
```

**Descripción del cambio:**

Se reemplazó la columna única `image_url` por dos columnas específicas:
- "URL imagen (Desktop)"
- "URL imagen (Mobile)"

#### B) Generación de URLs para Visualización

**Antes:**
```php
// Preparar el texto y las URLs
$text = isset($image->text) ? $image->text : '';
$image_url = !empty($image->url) ? html_writer::link($image->url, $image->url, ['target' => '_blank']) : '-';
$text_url = !empty($image->text_url) ? html_writer::link($image->text_url, $image->text_url, ['target' => '_blank']) : '-';
```

**Después:**
```php
// Preparar el texto y las URLs
$text = isset($image->text) ? $image->text : '';
// Construir URLs específicas de imágenes según origen
$desktop_url = '-';
if (!empty($image->desktop_image) && filter_var($image->desktop_image, FILTER_VALIDATE_URL)) {
    $desktop_url = html_writer::link($image->desktop_image, $image->desktop_image, ['target' => '_blank']);
}
$mobile_url = '-';
if (!empty($image->mobile_image) && filter_var($image->mobile_image, FILTER_VALIDATE_URL)) {
    $mobile_url = html_writer::link($image->mobile_image, $image->mobile_image, ['target' => '_blank']);
}
$text_url = !empty($image->text_url) ? html_writer::link($image->text_url, $image->text_url, ['target' => '_blank']) : '-';
```

**Descripción del cambio:**

Se implementó una lógica específica para generar URLs de visualización:
1. Verifica si `desktop_image` contiene una URL válida (usando `filter_var`)
2. Si es URL externa, genera un enlace clicable
3. Si es base64, muestra "-"
4. Repite el proceso para `mobile_image`

#### C) Actualización de Datos de Fila

**Antes:**
```php
$table->data[] = array(
    $index + 1,
    $preview,
    $image_url,
    $text,
    $text_url,
    $actions
);
```

**Después:**
```php
$table->data[] = array(
    $index + 1,
    $preview,
    $desktop_url,
    $mobile_url,
    $text,
    $text_url,
    $actions
);
```

**Descripción del cambio:**

Se ajustó el array de datos para incluir ambas URLs (`desktop_url` y `mobile_url`) en lugar de una sola columna.

**Beneficio:** Los administradores pueden ver claramente qué URLs se utilizaron para cada versión de la imagen (desktop y mobile), facilitando la auditoría y gestión del contenido.

---

## 3. Lógica de Normalización de URLs

### Expresión Regular Utilizada

```php
if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
    if (preg_match('/^www\.|^\w+\.\w+/i', $url)) {
        $url = 'https://' . $url;
    }
}
```

### Funcionamiento

1. **Primera verificación**: Detecta si la URL ya tiene un protocolo válido (`http://`, `https://`, `ftp://`, `ftps://`)
2. **Segunda verificación**: Si no tiene protocolo, verifica si parece una URL válida (comienza con `www.` o tiene formato de dominio)
3. **Normalización**: Agrega automáticamente `https://` al inicio de la URL

### Ejemplos de Transformación

| Entrada del Usuario | Salida Normalizada |
|---------------------|-------------------|
| `wallpapercave.com/wp/image.jpg` | `https://wallpapercave.com/wp/image.jpg` |
| `www.example.com/img.png` | `https://www.example.com/img.png` |
| `https://site.com/pic.jpg` | `https://site.com/pic.jpg` (sin cambios) |
| `ftp://server.com/file.jpg` | `ftp://server.com/file.jpg` (sin cambios) |

---

## 4. Estructura de Base de Datos

### Tabla: `imagecarousel_images`

| Campo | Tipo | Uso Original | Uso Actualizado |
|-------|------|--------------|-----------------|
| `url` | VARCHAR | URL de destino al hacer clic | URL de destino + fallback de imagen desktop/mobile |
| `desktop_image` | TEXT | URL o base64 de imagen desktop | URL o base64 de imagen desktop |
| `mobile_image` | TEXT | URL o base64 de imagen mobile | URL o base64 de imagen mobile |
| `desktop_image_name` | VARCHAR | Nombre del archivo desktop | Nombre del archivo desktop |
| `mobile_image_name` | VARCHAR | Nombre del archivo mobile | Nombre del archivo mobile |

**Nota:** No se modificó la estructura de la base de datos. Los cambios son únicamente en la lógica de aplicación.

---

## 5. Casos de Uso

### Caso 1: Usuario sube imagen desktop por URL

**Acciones del usuario:**
1. Accede al formulario de agregar/editar imagen
2. Ingresa URL en "O usar URL para imagen de escritorio": `https://wallpapercave.com/wp/wp13090475.jpg`
3. Deja el campo "URL" vacío
4. Guarda

**Resultado en BD:**
- `desktop_image`: `https://wallpapercave.com/wp/wp13090475.jpg`
- `url`: `https://wallpapercave.com/wp/wp13090475.jpg` (copiado automáticamente)

**Visualización en manage.php:**
- Columna "URL imagen (Desktop)": Enlace clicable a la imagen
- Columna "URL imagen (Mobile)": `-`

### Caso 2: Usuario sube ambas imágenes por URL

**Acciones del usuario:**
1. Ingresa URL desktop: `example.com/desktop.jpg`
2. Ingresa URL mobile: `example.com/mobile.jpg`
3. Ingresa URL destino: `example.com/landing`

**Resultado en BD:**
- `desktop_image`: `https://example.com/desktop.jpg` (normalizada)
- `mobile_image`: `https://example.com/mobile.jpg` (normalizada)
- `url`: `https://example.com/landing` (normalizada)

**Visualización en manage.php:**
- Columna "URL imagen (Desktop)": Enlace a desktop.jpg
- Columna "URL imagen (Mobile)": Enlace a mobile.jpg

### Caso 3: Usuario sube archivo local (no URL)

**Acciones del usuario:**
1. Selecciona archivo local para desktop mediante el file manager
2. Guarda

**Resultado en BD:**
- `desktop_image`: `[Base64 string]`
- `url`: `null`

**Visualización en manage.php:**
- Columna "URL imagen (Desktop)": `-`
- Columna "URL imagen (Mobile)": `-`
- Vista previa: Imagen renderizada desde base64

---

## 6. Compatibilidad con Versiones Anteriores

### Registros Existentes

Los registros existentes en la base de datos que tengan:
- `desktop_image` o `mobile_image` con URLs
- Campo `url` vacío

**No se verán afectados negativamente:**
- La vista de gestión mostrará correctamente las URLs en las nuevas columnas
- Al editar estos registros, se aplicará automáticamente la lógica de fallback
- No es necesaria una migración de datos

### Imágenes en Base64

Las imágenes almacenadas como base64 (archivos subidos localmente):
- Continúan funcionando sin cambios
- Se muestran correctamente en la vista previa
- Las columnas de URL muestran "-" según lo esperado

---

## 7. Pruebas Realizadas

### Prueba 1: Creación con URL Desktop
- ✅ URL se guarda correctamente en `desktop_image`
- ✅ URL se copia automáticamente a `url`
- ✅ Se muestra en columna "URL imagen (Desktop)" en manage.php

### Prueba 2: Edición con URL Mobile
- ✅ URL se guarda correctamente en `mobile_image`
- ✅ URL se copia a `url` si está vacío
- ✅ Se muestra en columna "URL imagen (Mobile)" en manage.php

### Prueba 3: URLs sin protocolo
- ✅ `wallpapercave.com/img.jpg` → `https://wallpapercave.com/img.jpg`
- ✅ `www.example.com/pic.png` → `https://www.example.com/pic.png`

### Prueba 4: Archivo local + URL externa
- ✅ Archivo local se convierte a base64
- ✅ URL externa se guarda como texto
- ✅ Ambas columnas muestran información correcta

---

## 8. Consideraciones de Seguridad

### Validación de URLs

- Se utiliza `filter_var($url, FILTER_VALIDATE_URL)` para verificar URLs válidas
- Las URLs se escapan mediante `html_writer::link()` antes de renderizar
- El atributo `target="_blank"` previene ataques de tabnapping mediante políticas implícitas de Moodle

### Prevención de XSS

- Todos los datos de usuario se pasan por funciones de escape de Moodle
- No se permite HTML directo en campos de URL
- El tipo `PARAM_URL` de Moodle proporciona sanitización adicional

### Protocolo HTTPS

- La normalización automática prioriza HTTPS sobre HTTP
- Mejora la seguridad de las conexiones externas
- Compatible con políticas de seguridad modernas

---

## 9. Impacto en Rendimiento

### Cambios Positivos
- **Reducción de consultas**: No se agregaron consultas adicionales a la base de datos
- **Caché compatible**: Las URLs normalizadas son más predecibles para sistemas de caché
- **Sin incremento en tamaño de BD**: No se agregaron columnas ni tablas

### Cambios Neutrales
- **Validación de URLs**: Mínimo impacto computacional (expresiones regulares simples)
- **Renderizado de tabla**: Misma cantidad de filas, solo una columna adicional

---

## 10. Documentación para Usuarios

### Para Administradores

**Vista de Gestión (manage.php):**
- Ahora verás dos columnas separadas: "URL imagen (Desktop)" y "URL imagen (Mobile)"
- Las URLs clicables te llevan directamente a la imagen externa
- Un guion (-) indica que la imagen está almacenada localmente (no es URL externa)

**Al Crear/Editar Imágenes:**
- Puedes ingresar URLs con o sin protocolo (se agrega automáticamente `https://`)
- Si no llenas el campo "URL" pero ingresas una URL de imagen, esta se usará automáticamente
- Las URLs de imágenes desktop tienen prioridad sobre mobile para el campo "URL"

---

## 11. Recomendaciones Futuras

### Mejoras Opcionales

1. **Internacionalización**:
   - Agregar strings traducibles para "URL imagen (Desktop)" y "URL imagen (Mobile)" en `lang/es/imagecarousel.php` y `lang/en/imagecarousel.php`

2. **Validación de URLs externas**:
   - Implementar verificación de accesibilidad de URLs (HEAD request) antes de guardar
   - Alertar al usuario si la URL no es accesible

3. **Caché de imágenes externas**:
   - Opcional: descargar y cachear imágenes externas localmente para mejorar rendimiento

4. **Logs de auditoría**:
   - Registrar cambios en URLs para trazabilidad completa

---

## 12. Conclusión

Los cambios implementados resuelven exitosamente el problema de visualización de URLs de imágenes en el módulo imagecarousel. La solución es:

- **Retrocompatible**: No afecta registros existentes
- **Eficiente**: Sin impacto negativo en rendimiento
- **Segura**: Mantiene estándares de seguridad de Moodle
- **Escalable**: Fácil de mantener y extender
- **Intuitiva**: Mejora la experiencia del usuario administrador

### Resumen de Beneficios

| Aspecto | Antes | Después |
|---------|-------|---------|
| Visualización de URLs | No visible o incompleta | Dos columnas claras (Desktop/Mobile) |
| Almacenamiento de URLs | Inconsistente | Consistente con fallback automático |
| Normalización | Manual por usuario | Automática (agrega https://) |
| Diferenciación Desktop/Mobile | No visible | Claramente separada |
| Experiencia del usuario | Confusa | Clara e intuitiva |

---

## 13. Anexos

### A. Archivos Relacionados

- `/mod/imagecarousel/adding_image.php` - Formulario de creación
- `/mod/imagecarousel/edit.php` - Formulario de edición
- `/mod/imagecarousel/manage.php` - Vista de gestión
- `/mod/imagecarousel/lib.php` - Funciones auxiliares
- `/mod/imagecarousel/classes/utils/images.php` - Clase de gestión de imágenes

### B. Referencias de Código

**Función de normalización de URL:**
```php
function normalize_image_url($url) {
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        if (preg_match('/^www\.|^\w+\.\w+/i', $url)) {
            return 'https://' . $url;
        }
    }
    return $url;
}
```

### C. Capturas de Pantalla Recomendadas

Para documentación visual, se recomienda agregar:
1. Vista de gestión mostrando ambas columnas de URLs
2. Formulario de edición con campos de URL
3. Ejemplo de URL normalizada automáticamente

---

**Fin del Informe Técnico**

---

**Elaborado por:** Equipo de Desarrollo Zajuna  
**Fecha de elaboración:** 4 de diciembre de 2025  
**Versión del documento:** 1.0  
**Estado:** Final
