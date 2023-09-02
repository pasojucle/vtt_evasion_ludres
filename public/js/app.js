import('./js-datepicker.js');
import('./reveal.js');
import('./input-file.js');
import('./constraints.js');
import('./switch.js');
import('./dropdown.js');
import('./clipboard.js');

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
    $( window ).scroll(function() {
        if ($(window).scrollTop() > 150) {
            $('nav:not(.paginator)').addClass('fixed');
            $('nav:not(.paginator) img').removeClass('hidden');
        } else {
            $('nav:not(.paginator)').removeClass('fixed');
            $('nav:not(.paginator) img').addClass('hidden');
        }
    });
    if ($('.sortable').length > 0) {
        buildSortable();
    }
    $(document).on('change', '.identity-other-address', updateIdentity);

    $(document).on('change', '.filters .customSelect2, .filters select, .filters .btn', submitFom);
    $(document).on('click', '.nav-bar .btn', toggleMenu);

    $(document).on('change', '.form-modifier', formModifier);
    $(document).on('click', 'button.form-modifier', formModifier);
    $(document).on('click', '.admin-session-present', adminSessionPresent);
    $(document).on('click', '.orderline-quantity, .orderline-remove', setOrderLineQuantity);
    $(document).on('click', '.cluster-complete', clusterComplete);
    $(document).on('click', '.order-status, .delete-error', anchorAsynchronous);
    $('.select2entity.submit-asynchronous').on('change', submitAsynchronous);
    $(document).on('click', '*[data-action="toggle-down"]', toggleDown);


    if (window.matchMedia("(min-width: 800px)").matches) {
        $(document).on('mouseenter', '.block-flash .block-title, .block-flash .block-body', addUp);
        $(document).on('mouseleave', '.block-flash .block-title, .block-flash .block-body', addDown);
    }
    initAddItemLink();
    const collectionItems = document.querySelectorAll('.collection_container .form-group-collection:not(.not-deleted)');
    collectionItems.forEach((item) => {
        if ($(item).find('input:disabled').length < 1) {
            addTagFormDeleteLink(item);
        } 
    })


    document.querySelectorAll('object.sizing').forEach(object => resize(object));

    if ($('.customSelect2').length > 0) {
        $('.customSelect2').select2();
    }
    
    $(document).on('customSelect2:open', (event) => {
        const id = event.target.id;
        document.querySelector('.select2-search__field[aria-controls="customSelect2-'+id+'-results"]').focus();
    });
    $(document).on('click', '#user_search_submit', confirmDeleteUser)
    if (document.querySelector('select#bike_ride_bikeRideType')){
        document.querySelector('select#bike_ride_bikeRideType').addEventListener('change', handleChangeBikeRideType)
    }
});

function confirmDeleteUser(e) {
            e.preventDefault();
        let form = $(this).closest('form');
        console.log(form.attr('action'));
        let user = form.find('#user_search_user').val();
        let route = Routing.generate('admin_tool_confirm_delete_user', {'user': user});
        let anchor = $('<a class="modal-trigger" href="'+route+'" data-toggle="modal" data-type="danger"></a>');
        form.append(anchor);
        console.log('anchor', anchor);
        anchor.click();
}


function updateIdentity(event) {
    let required = $(this).is(':checked');
    const container = $(this).parents('div.address-container');
    container.find('.address-group').toggleClass('hidden');
    if (required) {
        container.find('.identity-address').find('input').attr('required', 'required');
    } else {
        container.find('.identity-address').find('input').removeAttr('required');
        container.find('.identity-address').find('input').val('');
    }
}



function submitFom() {
    $(this).closest('form').submit()
}

function toggleMenu(e) {
    e.preventDefault();
    $('nav').toggleClass('nav-active');
    $('.nav-bar .btn').toggleClass('nav-hide');
    if($('nav').hasClass('nav-active')) {
        $('main').css('height', $('nav').height());
    } else {
        $('main').css('height', 'unset');
    }
    $('.block-body.down, .dropdown-toggle.down').each(function() {
        $(this).removeClass('down').addClass('up');
    });
    $('.fa-angle-up').each(function() {
        $(this).removeClass('fa-angle-up').addClass('fa-angle-down');
    });
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
    const target = event.target.dataset.modifier
    const data = new FormData(form, event.submitter);
    for(let entry of data) {
        if (entry[0].endsWith('[_token]')) {
            data.set(entry[0], '');
        }
    }
    if (event.target.type === 'button') {
        data.append(event.target.name, 1)
    }

    const url = form.getAttribute('action') ? form.getAttribute('action') : window.location.href;
    fetch(url, {
        method: form.getAttribute('method'),
        body : data,
    })
    .then((response) => response.text())
    .then((text)=> {
        const htmlElement = document.createRange().createContextualFragment(text);
        document.getElementById(target).replaceWith(
            htmlElement.getElementById(target)
        );
        $('.select2entity').select2entity();
        $('.customSelect2').select2();
        $('.js-datepicker').datepicker({
            format: 'yyyy-mm-dd hh:ii',
        });
        initAddItemLink();
    });
}

function clusterComplete(event) {
    event.preventDefault();
    const parameters = {};
    parameters['cluster'] = $(this).data('cluster-id');
    const route = Routing.generate('admin_cluster_complete', parameters);

    $.ajax({
        url : route,
        success: function(html) {
            $('#sessions_container').replaceWith($(html).find('#sessions_container'));
            $('a#cluster_export_'+parameters['cluster'])[0].click();
        }
      });
}


function adminSessionPresent(e) {
    e.preventDefault();

    $.ajax({
        url : $(this).attr('href'),
        success: function(html) {
            $('#sessions_container').replaceWith($(html).find('#sessions_container'));
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

function submitAsynchronous(e) {
    e.preventDefault();
    console.log('submitAsynchronous');
    const form = $(this).closest('form');
    let selector = 'form[name="'+form.attr('name')+'"]';
    let data = {};
    data[$(this).attr('name')] = $(this).val();
    $('.select2entity.submit-asynchronous').off('change', submitAsynchronous);
    $.ajax({
        url : form.attr('action'),
        type: form.attr('method'),
        data : form.serialize(),
        success: function(html) {
            $(selector).replaceWith($(html).find(selector));
            $('.select2entity').select2entity();
            $('.select2entity.submit-asynchronous').on('change', submitAsynchronous);
        }
      });
}

function toggleDown(e) {
    e.preventDefault();
    const icon = $(this).find('i');
    const block = $(this).closest('[data-toggle]');
    console.log('block', block);
    console.log('selecteur', '.block-body, *[data-target="'+block.data('toggle')+'"]');
    const blockBody = block.find('.block-body, *[data-target="'+block.data('toggle')+'"]');
    console.log('blockBody', blockBody);
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
    console.log(document.cookie);
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

const addFormToCollection = (e) => {
    const collectionHolder = document.querySelector('#' + e.currentTarget.dataset.collectionHolderClass);
    const container = collectionHolder.closest('.collection_container');
    const html = container
      .dataset
      .prototype
      .replace(
        /__name__/g,
        container.dataset.index
      );

    const item = document.createRange().createContextualFragment(html)
  
    collectionHolder.appendChild(item);
  
    container.dataset.index++;
    addTagFormDeleteLink(collectionHolder.lastChild);
  };

  const addTagFormDeleteLink = (itemForm) => {
    const removeFormButton = document.createElement('button');
    removeFormButton.classList.add('btn', 'btn-xs', 'btn-danger', 'col-md-1');
    removeFormButton.innerHTML ='<i class="fas fa-times"></i>';
    console.log('itemForm', itemForm)
    console.log('itemForm', $(itemForm))
    $(itemForm).append(removeFormButton);

    removeFormButton.addEventListener('click', (e) => {
        e.preventDefault()
        itemForm.remove();
    });
}

function resize(object) {
    const parent = object.parentNode;
    const computedStyle = getComputedStyle(parent);
    const width = parent.clientWidth - parseFloat(computedStyle.paddingLeft) - parseFloat(computedStyle.paddingRight);
    object.width = width;
    object.height = parent.dataset.ratio * width;
}

const initAddItemLink = () => {
    document.querySelectorAll('.add_item_link').forEach(btn => btn.addEventListener("click", addFormToCollection));
}

const handleChangeBikeRideType = () => {
    document.querySelector('#bike_ride_bikeRideTypeChanged').value = 1;
}