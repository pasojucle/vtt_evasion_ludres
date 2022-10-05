$(document).ready(function(){
    $(document).on('click', 'button:not(.dropdown-toggle), a[data-toggle="modal"], a.dropdown-item', hideDropdown);
    $(document).on('click', 'button.dropdown-toggle', toggleDropdown);
});


function toggleDropdown(event) {
    const dropdownButton = event.target;
    const dropdownMenu = dropdownButton.parentElement.querySelector('[data-target="'+dropdownButton.dataset.toggle +'"]');
    hideDropdown();
    dropdownButton.classList.toggle('active');
    dropdownMenu.classList.toggle('active');
    let buttonTop = dropdownButton.getBoundingClientRect().top;
    let wrapperTop = document.querySelector('.wrapper').getBoundingClientRect().top;
    let positionY = (wrapperTop > 0) ? wrapperTop : 0;
    let margingTop = buttonTop - positionY;
    let margingBottom = window.screen.height - Math.abs(wrapperTop)- buttonTop;
    let classMenu = (margingTop > dropdownMenu.offsetHeight && margingBottom < dropdownMenu.offsetHeight) ?'active-bottom' : 'active-top';
    dropdownMenu.classList.toggle(classMenu);
}

function hideDropdown() {
    document.querySelectorAll('.dropdown .dropdown-menu.active, button.dropdown-toggle.active').forEach((element) => {
        element.classList.remove('active');
        element.classList.remove('active-top');
        element.classList.remove('active-bottom');
    });
}