// app.js - navegación simple del front (solo UI)
// Hace que los enlaces del header muestren la sección correspondiente sin recargar.
// También maneja mostrar formulario de registro en la pestaña "account" cuando se pide.

document.addEventListener('DOMContentLoaded', function() {
  const navLinks = Array.from(document.querySelectorAll('.main-nav a'));
  const pages = Array.from(document.querySelectorAll('.page'));
  const menuToggle = document.getElementById('menuToggle');
  const mainNav = document.querySelector('.main-nav');

  function showPage(name) {
    pages.forEach(p => p.classList.toggle('active', p.id === name));
    navLinks.forEach(a => a.classList.toggle('active', a.dataset.target === name));
    window.location.hash = name;
    // scroll top
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  navLinks.forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      showPage(a.dataset.target);
      if (window.innerWidth < 900) mainNav.style.display = 'none';
    });
  });

  // cargar desde hash si existe
  const start = location.hash ? location.hash.replace('#','') : 'home';
  showPage(start);

  // menu mobile
  if (menuToggle) {
    menuToggle.addEventListener('click', () => {
      if (mainNav.style.display === 'flex') mainNav.style.display = 'none';
      else mainNav.style.display = 'flex';
    });
  }

  // si el link "Registrarme" es clicado (navegación desde index.php)
  const registerCard = document.getElementById('registerCard');
  if (location.search.includes('register')) {
    showPage('account');
    if (registerCard) registerCard.style.display = 'block';
  }
  // app.js
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('toggleSidebar');
  const sidebar = document.querySelector('.sidebar');

  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('active'); // en móvil
    sidebar.classList.toggle('collapsed'); // en PC
  });
});
});
