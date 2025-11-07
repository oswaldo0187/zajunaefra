
---
config:
  look: neo
  theme: default
---
flowchart TD
    A[Inicio] --> B{¿Crear nuevo carrusel?}
    B -->|Sí| C[Crear carrusel nuevo - mod_form.php]
    B -->|No| D{¿Ver carrusel existente?}
    C --> E[Configurar opciones del carrusel]
    E --> F[Añadir imágenes - adding_image.php]
    D -->|Sí| G[Ver carrusel - view.php]
    D -->|No| H{¿Gestionar carrusel?}
    H -->|Sí| I[Gestionar carrusel - manage.php]
    H -->|No| J{¿Editar carrusel?}
    I --> K[Listar imágenes]
    K --> L{¿Añadir imagen?}
    L -->|Sí| F
    L -->|No| M{¿Editar imagen?}
    M -->|Sí| N[Editar imagen - edit.php]
    M -->|No| O{¿Eliminar imagen?}
    O -->|Sí| P[Eliminar imagen - delete.php]
    O -->|No| Q{¿Reordenar?}
    Q -->|Sí| R[Cambiar orden]
    Q -->|No| K
    J -->|Sí| S[Editar configuración]
    J -->|No| T{¿Eliminar carrusel?}
    T -->|Sí| U[Eliminar carrusel]
    T -->|No| V[Fin]
    F --> V
    G --> V
    N --> K
    P --> K
    R --> K
    S --> V
    U --> V
