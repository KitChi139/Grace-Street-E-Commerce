let slideIndex = 0;
const INTERVAL = 5000;
let timer;

function getSlides() {
  return document.querySelectorAll('.slider img');
}

function goToSlide(n) {
  const slides = getSlides();
  slideIndex = (n + slides.length) % slides.length;
  const offset = slideIndex * slides[0].clientWidth * -1;
  document.querySelector('.slider').style.transform = `translateX(${offset}px)`;
  document.querySelectorAll('.gs-dot').forEach((d, i) =>
    d.classList.toggle('gs-dot-active', i === slideIndex)
  );
}

function nextSlide() { goToSlide(slideIndex + 1); }
function prevSlide() { goToSlide(slideIndex - 1); }

function startTimer() {
  clearInterval(timer);
  timer = setInterval(nextSlide, INTERVAL);
}

// Build dots + arrows dynamically
window.addEventListener('DOMContentLoaded', () => {
  const slides = getSlides();
  const container = document.querySelector('.slider-container');

  // Arrows
  const prev = document.createElement('button');
  prev.className = 'gs-arrow gs-prev';
  prev.innerHTML = '‹';
  prev.onclick = () => { prevSlide(); startTimer(); };

  const next = document.createElement('button');
  next.className = 'gs-arrow gs-next';
  next.innerHTML = '›';
  next.onclick = () => { nextSlide(); startTimer(); };

  // Dots
  const dotsWrap = document.createElement('div');
  dotsWrap.className = 'gs-dots';
  slides.forEach((_, i) => {
    const d = document.createElement('div');
    d.className = 'gs-dot' + (i === 0 ? ' gs-dot-active' : '');
    d.onclick = () => { goToSlide(i); startTimer(); };
    dotsWrap.appendChild(d);
  });

  container.appendChild(prev);
  container.appendChild(next);
  container.appendChild(dotsWrap);

  startTimer();
});