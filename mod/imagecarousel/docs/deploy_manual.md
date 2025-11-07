
# Manual de despliegue: Cambios en mod_imagecarousel

Fecha: 2025-10-23
Responsable: Equipo de despliegue / Administrador del sitio

Este manual describe los pasos para desplegar los cambios realizados en el módulo `mod_imagecarousel` en un entorno de staging o producción. Incluye verificación, purga de cachés, pruebas y rollback.

## Resumen de cambios
- `adding_image.php`: El texto explicativo se movió a iconos de ayuda mediante `addHelpButton()`.
- `lang/es/imagecarousel.php`: Se añadieron `desktop_image_info_help` y `mobile_image_info_help`.
- `lang/en/imagecarousel.php`: Se añadieron las mismas claves en inglés.

## Pre-requisitos
- Acceso SSH/PowerShell al servidor donde corre Moodle.
- Permisos para ejecutar scripts PHP desde CLI y para reiniciar servicios si es necesario.
- Copia de seguridad del código (git) y de la base de datos/archivos si el entorno es producción.

## Procedimiento de despliegue (paso a paso)
1. Preparar branch y commit
	- Asegúrate de que los cambios están en una branch o commit listo para deploy.
	- Ejemplo de comandos (en la raíz del repo):

```powershell
cd C:\wamp64\www\zajuna
git checkout -b feat/image-help-icons
git add mod/imagecarousel/adding_image.php \
	 mod/imagecarousel/lang/es/imagecarousel.php \
	 mod/imagecarousel/lang/en/imagecarousel.php
git commit -m "mod_imagecarousel: move image descriptions to help icons; add *_help strings (es/en)"
```

2. Desplegar a servidor (ejemplo con Git)
	- Dependiendo del flujo de despliegue, merge a la branch de release y desplegar.

3. En el servidor, después de actualizar archivos en el árbol de código, ejecutar:

```powershell
# Ir a la raíz de Moodle
cd C:\wamp64\www\zajuna
# Comprobar sintaxis de los archivos modificados
php -l "mod/imagecarousel/adding_image.php"
php -l "mod/imagecarousel/lang/es/imagecarousel.php"
php -l "mod/imagecarousel/lang/en/imagecarousel.php"
```

4. Purgar cachés de Moodle (obligatorio)

- Desde la UI: Administración del sitio -> Desarrollo -> Purge all caches
- O por CLI (recomendado en despliegue automatizado):

```powershell
php admin/cli/purge_caches.php
```

5. Verificar idioma activo
- Verificar la configuración de idioma del sitio (Administración del sitio -> Idioma -> Configuración del idioma).
- Si se utiliza una variante (ej: `es_mx`), confirmar que las cadenas `desktop_image_info_help` y `mobile_image_info_help` estén disponibles en esa variante. Si no, añadirlas en `mod/imagecarousel/lang/<variant>/imagecarousel.php` o usar Personalización de idioma (UI).

6. Pruebas funcionales (post-deploy)
- Abrir la página: `/mod/imagecarousel/adding_image.php?id=<cmid>`.
- Verificar:
  - El icono de ayuda aparece junto a "Imagen de escritorio" y "Imagen móvil".
  - Al hacer hover o focus en el icono, aparece el tooltip con la descripción larga (incluye el texto importante en negrita).
  - No aparece "TODO: missing help string".
- Revisar consola del navegador por errores JS o CSS.

7. Rollback (si detectas problemas)
- Si el cambio debe revertirse rápidamente, ejecutar:

```powershell
cd C:\wamp64\www\zajuna
# revertir al commit anterior (ejemplo revertiendo el commit por SHA)
git revert <commit-sha>
# o restaurar desde otra branch
# git checkout main && git reset --hard origin/main
```
- Purgar cachés de nuevo y verificar.

## Notas adicionales
- Si el sitio usa un CDN o cache a nivel HTTP, purgar el CDN después de desplegar para que los cambios de plantilla/JS/CSS se reflejen rápido.
- Considerar añadir pruebas Behat que verifiquen la presencia y contenido del help tooltip como parte del pipeline de QA.

## Checklist de verificación
- [ ] Archivos actualizados en repo
- [ ] Commit con mensaje claro
- [ ] Sintaxis PHP verificada (php -l) ✅
- [ ] Cachés purgados ✅
- [ ] Tooltip de ayuda visible y con contenido correcto ✅
- [ ] No aparece mensaje "TODO: missing help string" ✅

## Contacto
Si surgen dudas durante el despliegue, contactar a: equipo-dev@example.com

---

Fin del manual de despliegue.

