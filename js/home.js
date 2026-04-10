let slideIndex = 0;
carousel();

function carousel() {
    let slides = document.querySelectorAll('.slider img');
    if (slideIndex >= slides.length) {
        slideIndex = 0;
    }
    let slideWidth = slides[0].clientWidth;
    let offset = slideIndex * slideWidth * -1;
    document.querySelector('.slider').style.transform = `translateX(${offset}px)`;
    slideIndex++;
    setTimeout(carousel, 5000);
}

