
// Slider functionality
const slides = document.querySelector('.slider-container');
const buttons = document.querySelectorAll('.slide-btn');
let currentSlide = 0;

function changeSlide(index) {
  currentSlide = index;
  slides.style.transform = `translateX(-${index * 100}%)`;
  buttons.forEach((btn, i) => {
    btn.classList.toggle('bg-white', i === index);
    btn.classList.toggle('bg-gray-400', i !== index);
  });
}

// Event listener untuk tombol navigasi
buttons.forEach((btn, index) => {
  btn.addEventListener('click', () => changeSlide(index));
});

// Auto-slide (opsional)
setInterval(() => {
  currentSlide = (currentSlide + 1) % buttons.length;
  changeSlide(currentSlide);
}, 3000);


    const hamburger = document.getElementById('hamburger');
  const menu = document.getElementById('menu');
  const close = document.getElementById('close');

  // Open Menu
  hamburger.addEventListener('click', () => {
    menu.classList.remove('-translate-x-full'); // Menampilkan menu
  });

  // Close Menu
  close.addEventListener('click', () => {
    menu.classList.add('-translate-x-full'); // Menyembunyikan menu
  });



