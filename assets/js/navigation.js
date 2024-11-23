import { hideNotifications } from './notification.js';
import { disableScroll, enableScroll } from './toggleScroll.js';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.nav-bar .btn').forEach((element) => {
        element.addEventListener('click', toggleNav)
    })
    document.addEventListener("scroll", toggleFixed);

});


const toggleFixed = () => {
    const nav = document.querySelector('nav:not(.paginator)');
    if (window.scrollY > 150) {
        nav.classList.add('fixed');
        nav.querySelectorAll('img').forEach((img) => {
            img.classList.remove('hidden');
        })
    } else {
        nav.classList.remove('fixed');
        nav.querySelectorAll('img').forEach((img) => {
            img.classList.add('hidden');
        })
    }
}

const toggleNav =(event) => {
    event.preventDefault();
    const nav = document.querySelector('nav');
    nav.classList.toggle('nav-active')
    document.querySelector('.nav-bar .btn').classList.toggle('nav-hide');
    closeDropdown();
    
    if (nav.classList.contains('nav-active')) {
        hideNotifications();
        disableScroll();
        return;
    }
    enableScroll();
}

const closeDropdown = () => {
    document.querySelectorAll('.block-body.down, .dropdown-toggle.down').forEach((element) => {
        console.log('element', element)
        element.classList.replace('down', 'up')
    })

    document.querySelectorAll('.fa-angle-up').forEach((element) => {
        element.classList.replace('fa-angle-up', 'fa-angle-down');
    });
}

export const hideNav = () => {
    document.querySelector('nav').classList.remove('nav-active')
    document.querySelector('.nav-bar .btn').classList.remove('nav-hide');
    closeDropdown();
}