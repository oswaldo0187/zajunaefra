# Informe técnico: Visibilidad por imagen en el carrusel

## Resumen
Se agregó control de visibilidad por imagen en el módulo `mod_imagecarousel` para que instructores/administradores puedan mostrar u ocultar imágenes individuales sin eliminarlas. El carrusel ahora solo renderiza imágenes marcadas como visibles.

## Objetivo
- Permitir ocultar/mostrar imágenes sin perder su configuración.
- Evitar que imágenes desactivadas aparezcan en el slider del curso o en la vista del módulo.

## Cambios realizados
- **BD:** nuevo campo `visible` en `imagecarousel_images`; upgrade para añadirlo en instalaciones existentes.
- **Gestión:** en `manage.php` se añadió columna de visibilidad, badge de estado y acción toggle por imagen.
- **Creación:** `adding_image.php` crea imágenes con `visible = 1` por defecto.
- **Renderizado:** `view.php` y la integración en `lib.php` solo cargan imágenes visibles para mostrar en el carrusel.
- **Idiomas:** textos de visibilidad en `lang/en` y `lang/es`.
- **Versión:** se incrementó `version.php` para aplicar el upgrade.

## Archivos modificados
- `mod/imagecarousel/db/install.xml`
- `mod/imagecarousel/db/upgrade.php`
- `mod/imagecarousel/version.php`
- `mod/imagecarousel/manage.php`
- `mod/imagecarousel/adding_image.php`
- `mod/imagecarousel/view.php`
- `mod/imagecarousel/lib.php`
- `mod/imagecarousel/lang/en/imagecarousel.php`
- `mod/imagecarousel/lang/es/imagecarousel.php`
- `mod/imagecarousel/classes/utils/images.php`
- `mod/imagecarousel/edit.php`

## Archivos nuevos
- `mod/imagecarousel/INFORME_TECNICO_VISIBILIDAD.md` (este informe)

## Notas de despliegue
1) Ejecutar el upgrade de Moodle para crear el campo `visible` en la tabla `imagecarousel_images` (el upgrade incrementa la versión a `2025121500`).
2) Probar en `manage.php`: usar el toggle de visibilidad y verificar que el badge cambie.
3) Probar en el curso (`course/view.php`): confirmar que las imágenes ocultas no aparecen en el slider.

## Consideraciones
- Las imágenes existentes quedan visibles tras el upgrade.
- Nuevas imágenes se crean visibles por defecto.
