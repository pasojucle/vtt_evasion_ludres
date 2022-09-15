$(document).ready(function(){
    $(document).on('click', 'button:not(.dropdown-toggle), a[data-toggle="modal"]', hideDropdown);
    $(document).on('click', 'button.dropdown-toggle', toggleDropdown);
});


// function toggleDropdown(event) {
//     const dropdownButton = event.target;
//     const dropdownMenu = dropdownButton.parentElement.querySelector('[data-target="'+dropdownButton.dataset.toggle +'"]');
//     hideDropdown();
//     let buttonTop = dropdownButton.getBoundingClientRect().top;
//     let wrapperTop = document.querySelector('.wrapper').getBoundingClientRect().top;
//     let positionY = (wrapperTop > 0) ? wrapperTop : 0;
//     let margingTop = buttonTop - wrapperTop;
//     let margingBottom = window.screen.height - positionY - buttonTop - dropdownButton.offsetHeight;
//     let classMenu = (margingTop > dropdownMenu.offsetHeight && margingBottom < dropdownMenu.offsetHeight) ?'active-bottom' : 'active-top';
//     dropdownButton.classList.toggle('active');
//     dropdownMenu.classList.toggle('active');
//     dropdownMenu.classList.toggle(classMenu);
// }

// function hideDropdown() {
//     document.querySelectorAll('.dropdown .dropdown-menu.active, button.dropdown-toggle.active').forEach((element) => {
//         element.classList.remove('active');
//         element.classList.remove('active-top');
//         element.classList.remove('active-bottom');
//     });
// }


function toggleDropdown(event) {
    const dropdownButton = $(this);
    const dropdownMenu = dropdownButton.parent().find('[data-target="'+dropdownButton.data('toggle')+'"]');
    hideDropdown();
    let buttonTop = dropdownButton.offset().top;
    let wrapperTop = $('.wrapper').offset().top;
    let positionY = (wrapperTop > 0) ? wrapperTop : 0;
    let margingTop = buttonTop - wrapperTop;
    let margingBottom = window.screen.height - positionY - buttonTop - dropdownButton.offsetHeight;
    let classMenu = (margingTop > dropdownMenu.offsetHeight && margingBottom < dropdownMenu.offsetHeight) ?'active-bottom' : 'active-top';

    dropdownButton.toggleClass('active');
    dropdownMenu.toggleClass('active '+classMenu);
}

function hideDropdown() {
    $('.dropdown .dropdown-menu.active, button.dropdown-toggle.active').each(function() {
        $(this).removeClass('active active-top active-bottom');
    });
}