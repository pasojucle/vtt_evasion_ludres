$(document).ready(function(){
    $(document).on('click', 'button:not(.dropdown-toggle), a[data-toggle="modal"], a.dropdown-item', hideDropdown);
    $(document).on('click', 'button.dropdown-toggle', toggleDropdown);
});


function toggleDropdown(event) {
    const dropdownButton = event.target;
    const dropdownMenu = dropdownButton.parentElement.querySelector('[data-target="'+dropdownButton.dataset.toggle +'"]');
    const isActive = dropdownButton.classList.contains('active');
    hideDropdown();
    if (!isActive) {
        dropdownButton.classList.toggle('active');
        dropdownMenu.classList.toggle('active');
        let buttonTop = dropdownButton.getBoundingClientRect().top;
        let margingBottom = window.innerHeight - buttonTop;
        let classMenu = (margingBottom > dropdownMenu.offsetHeight) ? 'active-top' : 'active-bottom';
        dropdownMenu.classList.toggle(classMenu);
    }
}

function hideDropdown() {
    document.querySelectorAll('.dropdown .dropdown-menu.active, button.dropdown-toggle.active').forEach((element) => {
        element.classList.remove('active');
        element.classList.remove('active-top');
        element.classList.remove('active-bottom');
    });
}