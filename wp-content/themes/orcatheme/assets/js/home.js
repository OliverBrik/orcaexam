document.addEventListener('DOMContentLoaded', function () {
    const gallery = document.querySelector('.frontpage-hero__gallery');
    const mainImage = gallery?.querySelector('.frontpage-hero__image-wrap img');
    const thumbnails = gallery?.querySelectorAll('.frontpage-hero__gallery-strip [data-gallery-src]');

    if (!mainImage || !thumbnails?.length) {
        return;
    }

    const images = [
        {
            src: mainImage.src,
            alt: mainImage.alt
        },
        ...Array.from(thumbnails).map(function (thumbnail) {
            return {
                src: thumbnail.dataset.gallerySrc,
                alt: thumbnail.querySelector('img').alt
            };
        })
    ];

    let currentIndex = 0;

    setInterval(function () {
        currentIndex = (currentIndex + 1) % images.length;
        const nextImage = images[currentIndex];
        const preloadedImage = new Image();

        preloadedImage.onload = function () {
            mainImage.classList.add('is-changing');

            setTimeout(function () {
                mainImage.src = nextImage.src;
                mainImage.alt = nextImage.alt;

                requestAnimationFrame(function () {
                    mainImage.classList.remove('is-changing');
                });

                preloadedImage.onload = null;
                preloadedImage.removeAttribute('src');
            }, 900);
        };

        preloadedImage.src = nextImage.src;
    }, 5000);   
});
