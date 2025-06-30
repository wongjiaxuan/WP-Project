document.addEventListener("DOMContentLoaded", function() {
    const currentYear = new Date().getFullYear();
    document.querySelectorAll('#current-year').forEach(el => {
        el.textContent = currentYear;
    });
});

const menuIcon = document.getElementById('menuicon');
const menu = document.querySelector('.menu');
const nav = document.querySelector('nav');
    
menuIcon.addEventListener('click', () => {
    menu.classList.toggle('show');
    menuIcon.classList.toggle('rotate');
});

document.addEventListener('click', (event) => {
    if (!menu.contains(event.target) && !menuIcon.contains(event.target)) {
        // Hide the menu if the click is outside of the menu and icon
        menu.classList.remove('show');
        menuIcon.classList.remove('rotate');
    }
});

