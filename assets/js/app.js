
import { handleCheckChange, formToggle } from './form.js';
import { Form } from './formValidator.js';
import { addDeleteLink, initAddItemLink } from './entityCollection.js'
import { initInputFile } from './input-file.js';
import { switchEventListener } from './switch.js';

var formValidator;

document.addEventListener("DOMContentLoaded", (event) => {
    if ($('ul.StepProgress').length > 0) {
        const stepProgressMargingTop = parseInt($('ul.StepProgress').css('margin-top').replace('px', ''));
        const offsetTop = $('ul.StepProgress').offset().top-100;
        let marginTop = stepProgressMargingTop;
        $( window ).scroll(function() {
            if ($(window).scrollTop() >= offsetTop) {
                marginTop = $(window).scrollTop() - 200;
            } else {
                marginTop = stepProgressMargingTop;
            }
            $('ul.StepProgress').css('margin-top', marginTop+'px');
        });
    }

    if ($('.sortable').length > 0) {
        buildSortable();
    }

    $(document).on('change', '.filters select, .filters .btn, .filters input', submitFom);
    $(document).on('change', '.form-modifier', formModifier);
    $(document).on('click', 'button.form-modifier', formModifier);
    $(document).on('click', '.orderline-quantity, .orderline-remove', setOrderLineQuantity);
    $(document).on('click', '.order-status, .delete-error', anchorAsynchronous);
    $(document).on('click', '*[data-action="toggle-down"]', toggleDown);

    if (window.matchMedia("(min-width: 800px)").matches) {
        $(document).on('mouseenter', '.block-flash .block-title, .block-flash .block-body', addUp);
        $(document).on('mouseleave', '.block-flash .block-title, .block-flash .block-body', addDown);
    }
    
    initAddItemLink();
    addDeleteLink();

    document.querySelectorAll('object.sizing').forEach(object => resize(object));
    
    $(document).on('click', '#user_search_submit', confirmDeleteUser)
    addBikeRideTypeChangeListener();

    document.querySelectorAll('.check-toggle').forEach(element => {
        element.addEventListener('change', handleCheckChange);
        formToggle(element);
    })
    const form = document.querySelector('form');
    if (form) {
        formValidator = new Form(form);
    }

    document.querySelectorAll('.foreign-born').forEach((element) => {
        element.addEventListener('click', toggleBirthPlace);
    })
});

function confirmDeleteUser(e) {
    e.preventDefault();
    let form = $(this).closest('form');
    let user = form.find('#user_search_user').val();
    let route = Routing.generate('admin_tool_confirm_delete_user', {'user': user});
    let anchor = $('<a class="modal-trigger" href="'+route+'" data-toggle="modal" data-type="danger"></a>');
    form.append(anchor);
    anchor.click();
}

const toggleBirthPlace = () => {
    document.querySelectorAll('input[name$="[birthPlace]"], input[name$="[birthCountry]"], select[name$="[birthCommune]"]').forEach((element) => {
        const parent = element.closest('.birth-place')
        parent.classList.toggle('d-none');
        let required = true;
        if (parent.classList.contains('d-none')) {
            element.value = null;
            parent.querySelectorAll('.ts-control .clear-button').forEach((element) => {
                element.click();
            })
            required = false;
        }
        element.required = required;
    })
    formValidator.validate();
}

function submitFom() {
    const form = $(this).closest('form')[0];
    if (form) {
        form.requestSubmit();
    }
}

function buildSortable() {
    let error = false;
    $("ul.sortable").sortable({
        connectWith: ".connectedSortable",
        helper: "clone",
        cursor: "move",
        zIndex: 99999,
        items: "> li.ui-state-default",
        over: function (event, ui) {
            ui.item.addClass('sortable-over');
        },
        start: function (event, ui) {
            ui.item.data('old-order', ui.item.index());
            ui.item.addClass('sortable-over');
        },
        update: function (event, ui) {
            updateLinkOrder(ui.item);
        },
    });
}

function updateLinkOrder(item) {
    const id = item.data('id');
    const newOrder = item.index();
    const sortable = $(item).closest("ul.sortable");
    const parameters = {};
    parameters[sortable.data('parameter')] = id;
    const route = Routing.generate(sortable.data('route'), parameters);
    const data = {'newOrder' : newOrder};
    $.ajax({
        url : route,
        type: 'POST',
        data : data,
        success: function(html) {
            if(html) {
                sortable.replaceWith($(html).find('ul.sortable[data-target="'+sortable.data('target')+'"]'));
                buildSortable();
            }
        }
    });
}

function formModifier(event) {
    event.preventDefault();
    const form = event.target.closest('form');
    const target = event.target.dataset.modifier;
    const addToFetch = event.target.dataset.addToFetch;
    const data = new FormData(form, event.submitter);
    data.append(`${form.name}[handler]`, event.target.name)
    if (event.target.type === 'button') {
        data.append(event.target.name, 1)
    }
    if (addToFetch !== undefined) {
        data.append(`${form.name}[${addToFetch}]`, event.target.dataset[addToFetch]);
    }

    const url = form.getAttribute('action') ? form.getAttribute('action') : window.location.href;
    fetch(url, {
        method: form.getAttribute('method'),
        body : data,
    })
    .then((response) => response.text())
    .then((text)=> {
        const htmlElement = document.createRange().createContextualFragment(text);
        const targetEl = document.getElementById(target);
        targetEl.replaceWith(htmlElement.getElementById(target));
        $('.js-datepicker').datepicker({
            format: 'yyyy-mm-dd hh:ii',
        });
        initAddItemLink();
        initInputFile();
        addBikeRideTypeChangeListener();
        switchEventListener();
        targetEl.querySelectorAll('.form-modifier').forEach((element) => {
            element.addEventListener('change', formModifier);
        });
        if (formValidator) {
            formValidator.addEventListenerChange(targetEl);
        }
    });
}

function setOrderLineQuantity(e) {
    e.preventDefault();

    const form = $(this).closest('form');
    let data = {};
    data[$(this).attr('name')] = $(this).val();

    data[$(this).parent().data('lineName')] = $(this).parent().data('lineId');

    $.ajax({
        url : form.attr('action'),
        type: form.attr('method'),
        data : data,
        success: function(html) {
          $('#order').replaceWith(
            $(html).find('#order')
          );
        }
      });
}

function anchorAsynchronous(e) {
    e.preventDefault();
    const route = $(this).attr('href');
    const container = $(this).closest('ul').attr('id');
    const elementsToUpdate = ['#'+container, '.wrapper-title .badge.badge-info','nav.paginator.margin-top','nav.paginator.margin-bottom','nav.paginator.margin-both'];
    $.ajax({
        url : route,
        success: function(html) {
            elementsToUpdate.forEach((element) => {
                if ($(element)) {
                    $(element).replaceWith($(html).find(element));
                }
            });
        }
      });
}

function toggleDown(e) {
    e.preventDefault();
    const icon = $(this).find('i');
    const block = $(this).closest('[data-toggle]');
    const blockBody = block.find('.block-body, *[data-target="'+block.data('toggle')+'"]');
    blockBody.toggleClass('down').toggleClass('up');
    $('.down[data-target="'+block.data('toggle')+'"]').each(function() {
        if (!$(this).is(blockBody)) {
            $(this).removeClass('down').addClass('up');
        }
    });
    const regex = /up|down|fas|far|\s/g;
    const className = icon.attr('class').replace(regex, '');
    icon.toggleClass(className+'up').toggleClass(className+'down');
    $('.'+className+'up').each(function() {
        if (!$(this).is(icon)) {
            $(this).removeClass(className+'up').addClass(className+'down');
        }
    });
    const cookieValue =  (block.hasClass('nav-group') && blockBody.hasClass('down')) ? block.data('group') : null;
    // document.cookie = "admin_menu_actived = "+cookieValue;
    setCookie('admin_menu_actived', cookieValue, 30);
}
function setCookie(cName, cValue, expDays) {
    let date = new Date();
    date.setTime(date.getTime() + (expDays * 24 * 60 * 60 * 1000));
    const expires = "expires=" + date.toUTCString();
    document.cookie = cName + "=" + cValue + "; " + expires + "; path=/; url=" + location.hostname;
}

function addDown(e) {
    $(this).closest('div.block').find('i').removeClass('fa-caret-square-up').addClass('fa-caret-square-down');
}

function addUp(e) {
    $(this).closest('div.block').find('i').addClass('fa-caret-square-up').removeClass('fa-caret-square-down');
}

function resize(object) {
    const parent = object.parentNode;
    const computedStyle = getComputedStyle(parent);
    const width = parent.clientWidth - parseFloat(computedStyle.paddingLeft) - parseFloat(computedStyle.paddingRight);
    object.width = width;
    object.height = parent.dataset.ratio * width;
}

const addBikeRideTypeChangeListener = () => {
    if (document.querySelector('select#bike_ride_bikeRideType')){
        document.querySelector('select#bike_ride_bikeRideType').addEventListener('change', handleChangeBikeRideType)
    }
}

const handleChangeBikeRideType = () => {
    document.querySelector('#bike_ride_bikeRideTypeChanged').value = 1;
}