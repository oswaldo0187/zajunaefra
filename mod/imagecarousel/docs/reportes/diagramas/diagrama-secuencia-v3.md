sequenceDiagram
    actor Usuario
    participant Navegador
    participant view.php
    participant manage.php
    participant adding_image.php
    participant edit.php
    participant delete.php
    participant ImagesClass as classes/utils/images.php
    participant DB as Base de Datos

    Note over Usuario, DB: Flujo de Visualización
    Usuario->>Navegador: Accede al carrusel
    Navegador->>view.php: Solicita carrusel (GET)
    view.php->>ImagesClass: getImages(carouselid)
    ImagesClass->>DB: SELECT * FROM imagecarousel_images
    DB-->>ImagesClass: Devuelve registros
    ImagesClass-->>view.php: Devuelve array de imágenes
    view.php->>Navegador: Renderiza carrusel
    Navegador-->>Usuario: Muestra carrusel

    Note over Usuario, DB: Flujo de Administración
    Usuario->>Navegador: Accede a gestión
    Navegador->>manage.php: Solicita panel admin (GET)
    manage.php->>ImagesClass: getImages(carouselid)
    ImagesClass->>DB: SELECT * FROM imagecarousel_images
    DB-->>ImagesClass: Devuelve registros
    ImagesClass-->>manage.php: Devuelve array de imágenes
    manage.php->>Navegador: Renderiza panel admin
    Navegador-->>Usuario: Muestra listado de imágenes

    Note over Usuario, DB: Flujo de Agregar Imagen
    Usuario->>Navegador: Clic en "Agregar imagen"
    Navegador->>adding_image.php: Solicita formulario (GET)
    adding_image.php->>Navegador: Muestra formulario
    Usuario->>Navegador: Completa formulario y envía
    Navegador->>adding_image.php: Envía datos (POST)
    adding_image.php->>ImagesClass: saveImage(data, cm)
    ImagesClass->>DB: INSERT INTO imagecarousel_images
    DB-->>ImagesClass: Confirma inserción
    ImagesClass-->>adding_image.php: Devuelve ID de imagen
    adding_image.php->>Navegador: Redirige a manage.php
    Navegador-->>Usuario: Muestra listado actualizado

    Note over Usuario, DB: Flujo de Editar Imagen
    Usuario->>Navegador: Clic en "Editar" imagen
    Navegador->>edit.php: Solicita edición (GET + imageid)
    edit.php->>ImagesClass: getImage(carouselid, imageid)
    ImagesClass->>DB: SELECT * FROM imagecarousel_images WHERE id=?
    DB-->>ImagesClass: Devuelve registro
    ImagesClass-->>edit.php: Devuelve datos de imagen
    edit.php->>Navegador: Muestra formulario con datos
    Usuario->>Navegador: Modifica y envía
    Navegador->>edit.php: Envía datos (POST)
    edit.php->>ImagesClass: saveImage(data, cm)
    ImagesClass->>DB: UPDATE imagecarousel_images SET...
    DB-->>ImagesClass: Confirma actualización
    ImagesClass-->>edit.php: Devuelve resultado
    edit.php->>Navegador: Redirige a manage.php
    Navegador-->>Usuario: Muestra listado actualizado

    Note over Usuario, DB: Flujo de Eliminar Imagen
    Usuario->>Navegador: Clic en "Eliminar" imagen
    Navegador->>delete.php: Solicita confirmación (GET + imageid)
    delete.php->>ImagesClass: getImage(carouselid, imageid)
    ImagesClass->>DB: SELECT * FROM imagecarousel_images WHERE id=?
    DB-->>ImagesClass: Devuelve registro
    ImagesClass-->>delete.php: Devuelve datos de imagen
    delete.php->>Navegador: Muestra confirmación
    Usuario->>Navegador: Confirma eliminación
    Navegador->>delete.php: Envía confirmación (GET + confirm)
    delete.php->>DB: DELETE FROM imagecarousel_images WHERE id=?
    DB-->>delete.php: Confirma eliminación
    delete.php->>Navegador: Redirige a manage.php
    Navegador-->>Usuario: Muestra listado actualizado