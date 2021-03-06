$(document).ready(function(){
    getMediaScreen();
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
    $(document).on('change', 'input[type="file"]', previewFile);
    $('.js-datepicker').datepicker({
        format: 'yyyy-mm-dd hh:ii',
    });
    // $(document).on('change', '#bike_ride_filter_period, #user_filter_status, #user_filter_levels, #user_filter_user, #registration_filter_isFinal, #order_filter_status', submitFom);
    $(document).on('change', '.filters .select2, .filters select, .filters .btn', submitFom);
    $(document).on('click', '.nav-bar .btn', toggleMenu);
    $(document).on('click', '.input-file-button', getFile);
    $(document).on('change', '#bike_ride_bikeRideType', modifierBikeRide);
    $(document).on('change', '.form-modifier', formModifier);
    $(document).on('click', '.admin-session-present', adminSessionPresent);
    $(document).on('click', '.disease-active', toggleDisease);
    $(document).on('click', '.orderline-quantity, .orderline-remove', setOrderLineQuantity);
    $(document).on('click', '.cluster-complete', clusterComplete);
    $(document).on('click', '.order-status, .delete-error', anchorAsynchronous);
    $('.select2entity.submit-asynchronous').on('change', submitAsynchronous);
    $(document).on('click', '*[data-action="toggle-down"]', toggleDown);
    $(document).on('click', 'a[data-clipboard="1"]', clipboard);
    $(document).on('click', '.email-to-clipboard', emailToClipboard);
    $(document).on('click', 'button:not(.dropdown-toggle), a[data-toggle="modal"]', hideDropdown);
    $(document).on('click', 'button.dropdown-toggle', toggleDropdown);
    if (window.matchMedia("(min-width: 800px)").matches) {
        $(document).on('mouseenter', '.block-flash .block-title, .block-flash .block-body', addUp);
        $(document).on('mouseleave', '.block-flash .block-title, .block-flash .block-body', addDown);
    }
    document
        .querySelectorAll('.add_item_link')
        .forEach(btn => btn.addEventListener("click", addFormToCollection));
    const collectionItems = document.querySelectorAll('ul.collection_container > li')
    collectionItems.forEach((item) => {
        if ($(item).find('input:disabled').length < 1) {
            addTagFormDeleteLink(item);
        } 
    })
    if ($('#modal_window_show').length > 0) {
        $('#modal_window_show').click();
    }
    document.querySelectorAll('object.sizing').forEach(object => resize(object));
    document
        .querySelectorAll('.switch input[type="checkbox"]')
        .forEach(btn => btn.addEventListener("change", handleSwitch));
    if ($('.select2').length > 0) {
        $('.select2').select2();
    }
    
    $(document).on('select2:open', (event) => {
        const id = event.target.id;
        document.querySelector('.select2-search__field[aria-controls="select2-'+id+'-results"]').focus();
    });
});

jQuery(function($){
	$.datepicker.regional['fr'] = {
		closeText: 'Fermer',
		prevText: '&#x3c;Pr??c',
		nextText: 'Suiv&#x3e;',
		currentText: 'Aujourd\'hui',
		monthNames: ['Janvier','Fevrier','Mars','Avril','Mai','Juin',
		'Juillet','Aout','Septembre','Octobre','Novembre','Decembre'],
		monthNamesShort: ['Jan','Fev','Mar','Avr','Mai','Jun',
		'Jul','Aou','Sep','Oct','Nov','Dec'],
		dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
		dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
		dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
		weekHeader: 'Sm',
		dateFormat: 'dd/mm/yy',
        timeFormat:  "HH:mm",
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: '',
		minDate: '-12M +0D',
		maxDate: '+12M +0D',
		numberOfMonths: 1,
		showButtonPanel: false
	};
	$.datepicker.setDefaults($.datepicker.regional['fr']);
});

function updateIdentity() {
    let required = $(this).is(':checked');
    const container = $(this).parents('div.address-container');
    console.log(container);
    console.log(container.find('.identity-address'));
    container.find('.identity-address').toggleClass('hidden');
    if (required) {
        container.find('.identity-address').find('input').attr('required', 'required');
    } else {
        container.find('.identity-address').find('input').removeAttr('required');
        container.find('.identity-address').find('input').val('');
    }
}

function previewFile() {
    const previews = $(this).parent().parent().find('img, canvas');
    const [file] = this.files;
    if (file) {
        const image = URL.createObjectURL(file);
        previews.each(function() {
            if (this instanceof HTMLImageElement) {
                this.src = image;
            }
            if (this instanceof HTMLCanvasElement) {
                this.dataset.src =  image;
            }
            
        });
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

function getMediaScreen() {
    const width = screen.width;
    let mediaScreen = (width > 800) ? 'md' : 'xs';
    setCookie('media_screen', mediaScreen, 30)
}

function getFile(e) {
    e.preventDefault();
    $inputFile = $('input[type="file"]');
    $inputFile.click();
    $inputFile.on('change',  function(event) {
        filename = event.target.value.split('\\').pop();
        $('#filename').text(filename);
    });
    return false;
}

function modifierBikeRide() {
    var form = $(this).closest('form');
    var data = {};
    $.each(form[0].elements,function() {
        if ($(this).attr('type') !== 'hidden') {
            data[$(this).attr('name')] = $(this).val();
        }
    });

    $.ajax({
        url : form.attr('action'),
        type: form.attr('method'),
        data : data,
        success: function(html) {
          $('#bike_ride_container').replaceWith(
            $(html).find('#bike_ride_container')
          );
          $('.js-datepicker').datepicker({
            format: 'yyyy-mm-dd hh:ii',
        });
        }
      });
}

function formModifier() {
    const form = $(this).closest('form');
    const selector = '#' + $(this).data('modifier');
    const data = {};
    $.each(form[0].elements,function() {
        if ($(this).attr('type') === 'radio' && $(this).is(':checked') || $(this).attr('type') !== 'radio' && $(this).attr('type') !== 'hidden') {
            data[$(this).attr('name')] = $(this).val();
        }
    });

    $.ajax({
        url : form.attr('action'),
        type: form.attr('method'),
        data : data,
        success: function(html) {
          $(selector).replaceWith(
            $(html).find(selector)
          );
          $('.select2entity').select2entity();
        }
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

function toggleDisease() {
    const parent = $(this).closest('div.form-group');
    parent.find('input[type="text"]').toggleClass('disabled');
    if(!$(this).is(':checked')) {
        parent.find('input[type="text"]').removeAttr('required').removeAttr('required').val('');

    } else {
        parent.find('input[type="text"]').attr('required', true);
    }
}


function setOrderLineQuantity(e) {
    e.preventDefault();

    const form = $(this).closest('form');
    let data = {};
    data[$(this).attr('name')] = $(this).val();
    const id = $(this).parent().find('input[type="hidden"]');
    data[id.attr('name')] = id.val();

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

    $.ajax({
        url : route,
        success: function(html) {
            $('#'+container).replaceWith($(html).find('#'+container));
        }
      });
}

function submitAsynchronous(e) {
    e.preventDefault();
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
    console.log(document.cookie);
}
function setCookie(cName, cValue, expDays) {
    let date = new Date();
    date.setTime(date.getTime() + (expDays * 24 * 60 * 60 * 1000));
    const expires = "expires=" + date.toUTCString();
    document.cookie = cName + "=" + cValue + "; " + expires + "; path=/; url=" + location.hostname;
}

function clipboard(event) {
    event.preventDefault();
    const value = $(this).attr('href');
    navigator.clipboard.writeText(value);
}

function emailToClipboard(event) {
    event.preventDefault();
    const url = event.target.getAttribute('href');
    fetch(url).then(function (response) {
        return response.json();
    }).then(function (data) {
        navigator.clipboard.writeText(data);
        hideDropdown();
    });
}

function addDown(e) {
    $(this).closest('div.block').find('i').removeClass('fa-caret-square-up').addClass('fa-caret-square-down');
}

function addUp(e) {
    $(this).closest('div.block').find('i').addClass('fa-caret-square-up').removeClass('fa-caret-square-down');
}

const addFormToCollection = (e) => {
    const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);
  
    const item = document.createElement('li');
  
    item.innerHTML = collectionHolder
      .dataset
      .prototype
      .replace(
        /__name__/g,
        collectionHolder.dataset.index
      );
  
    collectionHolder.appendChild(item);
  
    collectionHolder.dataset.index++;
    addTagFormDeleteLink(item);
  };

  const addTagFormDeleteLink = (itemFormLi) => {
    const row = $(itemFormLi).find('.row');
    const removeFormButton = document.createElement('button');
    removeFormButton.classList.add('btn', 'btn-xs', 'btn-danger', 'col-md-1');
    removeFormButton.innerHTML ='<i class="fas fa-times"></i>';
    
    row.append(removeFormButton);

    removeFormButton.addEventListener('click', (e) => {
        e.preventDefault()
        // remove the li for the tag form
        itemFormLi.remove();
    });
}

function resize(object) {
    const parent = object.parentNode;
    const computedStyle = getComputedStyle(parent);
    const width = parent.clientWidth - parseFloat(computedStyle.paddingLeft) - parseFloat(computedStyle.paddingRight);
    object.width = width;
    object.height = parent.dataset.ratio * width;
}

function toggleDropdown(event) {
    const dropdownButton = $(this);
    const dropdownMenu = dropdownButton.parent().find('[data-target="'+dropdownButton.data('toggle')+'"]');
    $('.dropdown .dropdown-menu.active, button.dropdown-toggle.active').each(function() {
        if ($(this).data('target') !== dropdownMenu.data('target') && $(this).data('toggle') !== dropdownButton.data('toggle') ) {
            $(this).removeClass('active active-top active-bottom');
        }
    });
    let classMenu = (dropdownButton.offset().top + dropdownMenu.height() - $(window).scrollTop() < $(window).height()) ? 'active active-top' : 'active active-bottom';
    dropdownButton.toggleClass('active');
    dropdownMenu.toggleClass(classMenu);
}

function hideDropdown() {
    $('.dropdown .dropdown-menu.active, button.dropdown-toggle.active').each(function() {
        $(this).removeClass('active active-top active-bottom');
    });
}

function handleSwitch(event) {
    const swicthLabel = document.querySelector('label[for="'+event.target.id+'"]');
    if (event.target.dataset.switchOn && event.target.dataset.switchOff ) {
       swicthLabel.innerHTML =  (event.target.checked) ? event.target.dataset.switchOn : event.target.dataset.switchOff; 
    }
}