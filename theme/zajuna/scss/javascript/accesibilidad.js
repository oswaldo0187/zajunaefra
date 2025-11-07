const toggleInicioButton = document.getElementById('toggle-inicio');
const toggleButtons = document.getElementById('toggle-buttons');
const toggleContrastButton = document.getElementById('toggle-contrast');
const toggleContrasteButtons = document.getElementById('toggle-contraste-buttons');
const toggleZoomInButton = document.getElementById('toggle-zoom-in');
const toggleZoomOutButton = document.getElementById('toggle-zoom-out');
const toggleDislexiaButton = document.getElementById('toggle-dislexia');
const toggleContrastPropioButton = document.getElementById('toggle-contrast-propio');
const toggleLectorButton = document.getElementById('toggle-lector'); // Botón para el asistente de voz
const mensajeVoz = document.getElementById('mensaje-voz');
const rootElement = document.documentElement;

const contrastTypes = document.querySelectorAll('.contrast_types');

// Mostrar/ocultar panel de botones de accesibilidad
toggleInicioButton.addEventListener('click', () => {
    toggleButtons.classList.toggle('visible');
});

// Alternar contraste y mostrar opciones de contraste
toggleContrastButton.addEventListener('click', () => {
    toggleContrasteButtons.classList.toggle('visible');
});

// Alternar contraste con el botón específico (toggle-contrast-propio)
toggleContrastPropioButton.addEventListener('click', () => {
    const isContrastEnabled = rootElement.classList.toggle('contrast');
    applyImageContrast();
    localStorage.setItem('contrastSetting', isContrastEnabled ? 'contrast' : 'none');
});

// Asignar o quitar contrastes según la opción seleccionada (A, B, C)
contrastTypes.forEach((button, index) => {
    button.addEventListener('click', () => {
        const contrastClasses = ['contrast-a', 'contrast-b', 'contrast-c'];
        const currentClass = contrastClasses[index];

        if (rootElement.classList.contains(currentClass)) {
            rootElement.classList.remove(currentClass);
        } else {
            rootElement.classList.remove(...contrastClasses);
            rootElement.classList.add(currentClass);
        }
        applyImageContrast();
        localStorage.setItem('contrastSetting', currentClass);
    });
});

// Función para aplicar el contraste a las imágenes
function applyImageContrast() {
    const images = document.querySelectorAll('iframe, img:not(.entidades__link-img):not(.entidades__link-img:hover):not([src="img/icons/accesibilidad-contraste.svg"])');
    
    images.forEach(img => {
        if (rootElement.classList.contains('contrast')) {
            img.style.filter = 'grayscale(0%)';
        } else if (rootElement.classList.contains('contrast-a')) {
            img.style.filter = 'grayscale(100%)';
        } else if (rootElement.classList.contains('contrast-b')) {
            img.style.filter = 'grayscale(100%)';
        } else if (rootElement.classList.contains('contrast-c')) {
            img.style.filter = 'grayscale(100%)';
        } else {
            img.style.filter = 'none';
        }
    });
}

// Alternar fuente apta para dislexia
toggleDislexiaButton.addEventListener('click', () => {
    rootElement.classList.toggle('dyslexia-friendly');
    const isDyslexiaFriendly = rootElement.classList.contains('dyslexia-friendly');
    localStorage.setItem('dyslexiaSetting', isDyslexiaFriendly);
    
    if (isDyslexiaFriendly) {
        document.body.style.fontFamily = "'OpenDyslexic', sans-serif";
        document.body.style.letterSpacing = '0.1em';
        document.body.style.wordSpacing = '0.15em';
        document.body.style.lineHeight = '1.6';
    } else {
        document.body.style.fontFamily = '';
        document.body.style.letterSpacing = '';
        document.body.style.wordSpacing = '';
        document.body.style.lineHeight = '';
    }
});

// Aumentar tamaño de fuente
toggleZoomInButton.addEventListener('click', () => {
    let fontSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
    fontSize += 1;
    fontSize = Math.min(20, fontSize);
    document.documentElement.style.fontSize = fontSize + 'px';
    localStorage.setItem('fontSize', fontSize);
});

// Disminuir tamaño de fuente
toggleZoomOutButton.addEventListener('click', () => {
    let fontSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
    fontSize -= 1;
    fontSize = Math.max(13, fontSize);
    document.documentElement.style.fontSize = fontSize + 'px';
    localStorage.setItem('fontSize', fontSize);
});

// Cargar configuraciones desde localStorage al iniciar
document.addEventListener('DOMContentLoaded', () => {
    const storedContrast = localStorage.getItem('contrastSetting');
    const storedDyslexia = localStorage.getItem('dyslexiaSetting');
    const storedFontSize = localStorage.getItem('fontSize');

    if (storedContrast && storedContrast !== 'none') {
        rootElement.classList.add(storedContrast);
        applyImageContrast();
    }

    if (storedDyslexia === 'true') {
        rootElement.classList.add('dyslexia-friendly');
        document.body.style.fontFamily = "'OpenDyslexic', sans-serif";
        document.body.style.letterSpacing = '0.1em';
        document.body.style.wordSpacing = '0.15em';
        document.body.style.lineHeight = '1.6';
    }

    if (storedFontSize) {
        document.documentElement.style.fontSize = storedFontSize + 'px';
    }
});

// Cerrar los menús cuando se hace clic fuera de ellos
document.addEventListener('click', (event) => {
    const isClickInside = toggleButtons.contains(event.target) || toggleInicioButton.contains(event.target);

    if (!isClickInside) {
        toggleButtons.classList.remove('visible');
        toggleContrasteButtons.classList.remove('visible');
    }
});

// Mostrar mensaje al pasar el cursor
toggleLectorButton.addEventListener('mouseover', () => {
    mensajeVoz.style.display = 'block'; // Mostrar el mensaje
});

// Ocultar mensaje al quitar el cursor
toggleLectorButton.addEventListener('mouseout', () => {
    mensajeVoz.style.display = 'none'; // Ocultar el mensaje
});

toggleLectorButton.addEventListener('click', () => {
    if ('speechSynthesis' in window) {
        // Si ya se está hablando, detenemos la síntesis actual
        if (window.speechSynthesis.speaking) {
            window.speechSynthesis.cancel();
        }

        const mensaje = new SpeechSynthesisUtterance('Bienvenido. Este es un lector de pantalla diseñado para personas con discapacidad visual. Si estás en Windows, puedes activar el Narrador presionando Control más Windows más Enter. Usa las flechas para moverte entre secciones y enlaces.');
        mensaje.lang = 'es-ES';
        mensaje.rate = 1;
        mensaje.pitch = 1;

        window.speechSynthesis.speak(mensaje);
    } else {
        console.log('El navegador no soporta la API de síntesis de voz.');
    }
});

// Función para restaurar la página a su estado original, sin modificaciones de accesibilidad
function restaurarAccesibilidad() {
    // Remover todas las clases relacionadas con accesibilidad
    rootElement.classList.remove('contrast', 'contrast-a', 'contrast-b', 'contrast-c', 'dyslexia-friendly');
    
    // Restaurar el filtro de las imágenes
    const images = document.querySelectorAll('iframe, img:not(.entidades__link-img):not(.entidades__link-img:hover):not([src="img/icons/accesibilidad-contraste.svg"])');
    images.forEach(img => {
        img.style.filter = 'none';
    });

    // Restaurar la fuente y el espaciado de texto al valor predeterminado
    document.body.style.fontFamily = '';
    document.body.style.letterSpacing = '';
    document.body.style.wordSpacing = '';
    document.body.style.lineHeight = '';

    // Restaurar el tamaño de fuente al valor predeterminado
    document.documentElement.style.fontSize = '';

    // Limpiar las configuraciones guardadas en localStorage
    localStorage.removeItem('contrastSetting');
    localStorage.removeItem('dyslexiaSetting');
    localStorage.removeItem('fontSize');

    // Restaurar visibilidad de los paneles de botones
    const toggleButtons = document.getElementById('toggle-buttons');
    const toggleContrasteButtons = document.getElementById('toggle-contraste-buttons');
    toggleButtons.classList.remove('visible');
    toggleContrasteButtons.classList.remove('visible');
}

// Asignar la función restaurarAccesibilidad al botón de restauración
const toggleRestoreButton = document.getElementById('toggle-restore');
toggleRestoreButton.addEventListener('click', restaurarAccesibilidad);
