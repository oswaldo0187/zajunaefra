## Manual técnico: Validación de Disponibilidad / Caducidad en el módulo Image Carousel

Fecha: 29-10-2025

Resumen
-------
Se ha añadido una validación del lado servidor al formulario de edición del módulo `imagecarousel` para evitar que la fecha/hora de caducidad (`availableuntil`) sea anterior a la fecha/hora de disponibilidad (`availablefrom`). Esto evita que un administrador o docente configure accidentalmente un rango inválido que puede provocar confusión en la visibilidad del recurso.

Objetivo
--------
- Prevenir datos inconsistentes en la programación del módulo.
- Mostrar un mensaje de error localizado cuando el usuario intente guardar una configuración inválida.

Archivos modificados
--------------------
- `mod/imagecarousel/mod_form.php`
  - Se añadió el método `validation($data, $files)` que realiza la comprobación de que, cuando ambos campos están rellenados, `availableuntil >= availablefrom`. Si no se cumple, se añade un error en el campo `availableuntil`.

- `mod/imagecarousel/lang/es/imagecarousel.php`
  - Añadida la cadena: `availableuntil_error` con el mensaje en español.

- `mod/imagecarousel/lang/en/imagecarousel.php`
  - Añadida la cadena de fallback `availableuntil_error` en inglés para evitar la visualización de claves sin traducir (`[[availableuntil_error]]`) si el paquete de idioma no estuviera cargado correctamente.

Fragmento relevante (explicación)
--------------------------------
La validación se realiza dentro de la clase `mod_imagecarousel_mod_form` extendida de `moodleform_mod` y utiliza la firma estándar `validation($data, $files)` que devuelve un array asociativo `fieldname => errormessage`.

No se reemplazó el comportamiento de los selectores de fecha/hora de Moodle; la validación es complementaria y se realiza cuando el formulario es enviado al servidor.

Despliegue (pasos recomendados)
-------------------------------
1. Preparación y backups
   - Crear una rama para el cambio:

     git checkout -b fix/imagecarousel-availableuntil-validation

   - Hacer respaldo de la base de datos y ficheros (mínimo la carpeta `mod/imagecarousel` y la DB de Moodle):

     # Ejemplo (PowerShell)
     # Exportar DB (ajusta credenciales según tu entorno)
     mysqldump -u root -p moodle_db > C:\backups\moodle_db_$(Get-Date -Format yyyyMMdd_HHmm).sql

   - (Opcional) Exportar copia del directorio del plugin:

     xcopy /E /I c:\wamp64\www\zajuna\mod\imagecarousel C:\backups\imagecarousel_backup\

2. Aplicar cambios en el repositorio
   - Commit y push a la rama:

     git add mod/imagecarousel/mod_form.php mod/imagecarousel/lang/es/imagecarousel.php mod/imagecarousel/lang/en/imagecarousel.php
     git commit -m "Add validation: availableuntil cannot be earlier than availablefrom in imagecarousel form"
     git push origin fix/imagecarousel-availableuntil-validation

   - Abrir Pull Request en la plataforma de control de versiones (GitHub/GitLab) y solicitar revisión.

3. Despliegue en entorno de pruebas
   - Mergear a la rama de pruebas y desplegar.
   - Purga de cachés de Moodle (sólo para forzar recarga de cadenas de idioma y formularios):

     # En la interfaz: Administración del sitio > Desarrollo > Vaciar todas las cachés
     # O en CLI (desde el root de Moodle):
     php admin/cli/purge_caches.php

   - Si el idioma no muestra la cadena en producción, puede que haya que purgar caches de idioma. La ruta por UI también sirve: Administración del sitio > Desarrollo > Vaciar todas las cachés.

4. Verificación en entorno de pruebas
   - Ir a un curso, editar o crear una instancia del módulo 'Carrusel de imágenes' (`Image Carousel`).
   - En la sección Disponibilidad, elegir un `Available from` posterior a `Available until` (ej: Available from = 24/11/2025 15:48 y Available until = 28/01/2025 16:14) y pulsar "Guardar cambios".
   - Resultado esperado: el formulario se recarga y aparece el mensaje localizado al lado del campo "Fecha y hora de caducidad".

   Mensaje esperado (Español):

   > La fecha y hora de caducidad no puede ser anterior a la fecha y hora de disponibilidad.

Rollback (si es necesario)
-------------------------
- Revertir el commit o la fusión en la rama desplegada:

  git revert <commit-hash>
  git push origin <branch>

- Restaurar la copia de seguridad de la base de datos y los ficheros si el cambio causara efectos adversos.

Notas técnicas y consideraciones
--------------------------------
- El validador servidor-side es la protección esencial; sin embargo, para mejor UX se recomienda añadir validación cliente (JS) que compare los selectores de fecha antes del envío y muestre un mensaje inmediato.
- Las diferencias horarias entre usuarios no influyen en los timestamps internos de Moodle: los selectores devuelven valores en timestamp (INT) que se comparan numéricamente.
- Asegurarse de purgar la caché de idioma si se añade/edita una cadena; de lo contrario, Moodle puede mostrar la clave (`[[availableuntil_error]]`) en lugar del texto traducido.

Checklist para subida a producción
----------------------------------
- [ ] Respaldo de la DB realizado
- [ ] Backup del directorio del plugin
- [ ] PR abierto y revisado
- [ ] Cambios desplegados en staging y verificados
- [ ] Cachés purgados
- [ ] Monitoreo activo por 30 minutos después del despliegue

Registro de cambios (ejemplo)
----------------------------
- 2025-10-29: Añadida validación server-side y cadenas de idioma (es/en) para evitar que `availableuntil < availablefrom`.

Contacto
--------
Para dudas respecto a este cambio, contacta con el autor del plugin o con el equipo responsable del LMS.

Fin del manual.
