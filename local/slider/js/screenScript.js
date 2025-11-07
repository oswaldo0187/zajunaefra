const settings = {

    sizes: {

        mobile: 768

    },
    imagesByDevice: {

        desktop: '',
        mobile: ''

    }

}

function generateImagesForScreens() {

    const { imagesByDevice } = settings;

    const slides = Array.from(document.querySelectorAll('.swiper-slide'));

    settings.imagesByDevice = slides.reduce((acc, slide) => {
    
        const { dataset: images } = slide;

        const devices = Object.keys(images);
        
        devices.forEach((device) => {

            const { [device]: imageDevice } = images; 

            let tempSlide = slide; 
            const img = tempSlide.querySelector('img');

            img.src = `data:image/jpeg;base64,${imageDevice}`;

            tempSlide = tempSlide.outerHTML;

            tempSlide = tempSlide.substring((tempSlide.indexOf('>') + 1));

            tempSlide = `<div class="swiper-slide">${tempSlide}`;

            acc[device] += tempSlide;

        });

        return acc;

    }, imagesByDevice);

    loadImagesAcordingScreenSize();
 
}

function loadImagesAcordingScreenSize() {

    const screenWidth = window.innerWidth;

    const { imagesByDevice: { desktop, mobile } } = settings;

    const swiperWrapper = document.querySelector('.swiper-wrapper');

    const images = ((screenWidth > 768)) ? desktop : mobile

    swiperWrapper.innerHTML = images;

}

document.addEventListener('DOMContentLoaded', function() {

    generateImagesForScreens();

    window.addEventListener('resize', loadImagesAcordingScreenSize);

});