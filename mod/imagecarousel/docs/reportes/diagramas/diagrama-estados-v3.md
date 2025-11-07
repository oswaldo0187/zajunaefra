stateDiagram-v2
    [*] --> Creado: Se crea carrusel
    Creado --> ConfigurandoCarrusel: Configurar opciones
    ConfigurandoCarrusel --> AñadiendoImágenes: Añadir imágenes
    ConfigurandoCarrusel --> EditandoCarrusel: Editar configuración
    EditandoCarrusel --> ConfigurandoCarrusel: Guardar cambios
    
    AñadiendoImágenes --> EditandoImágenes: Editar metadatos
    AñadiendoImágenes --> OrdenandoImágenes: Cambiar orden
    EditandoImágenes --> AñadiendoImágenes: Guardar cambios
    OrdenandoImágenes --> AñadiendoImágenes: Guardar orden
    
    AñadiendoImágenes --> Publicado: Completar configuración
    EditandoCarrusel --> Publicado: Guardar cambios
    
    Publicado --> Visualizando: Usuario visualiza
    Publicado --> EditandoCarrusel: Administrador edita
    Publicado --> EliminandoCarrusel: Eliminar carrusel
    
    EliminandoCarrusel --> [*]: Carrusel eliminado