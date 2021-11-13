$(document).ready(function(){
    getMediaScreen();
    if ($('ul.StepProgress').length > 0) {
        const stepProgressMargingTop = parseInt($('ul.StepProgress').css('margin-top').replace('px', ''));
        const offsetTop = $('ul.StepProgress').offset().top-100;
        let marginTop = stepProgressMargingTop;
        $( window ).scroll(function() {
            if ($(window).scrollTop() >= offsetTop) {
                console.log($(window).scrollTop() - offsetTop);
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
    $(document).on('change', '#event_filter_period, #user_filter_status, #user_filter_level, #registration_filter_isFinal, #order_filter_status', submitFom);
    $(document).on('click', '.nav-bar .btn', toggleMenu);
    $(document).on('click', '.input-file-button', getFile);
    $(document).on('change', '#event_type', modifierEvent);
    $(document).on('click', '.admin-session-present', adminSessionPresent);
    $(document).on('click', '.disease-active', toggleDisease);
    $(document).on('click', '.orderline-quantity, .orderline-remove', setOrderLineQuantity);
    $(document).on('click', '.cluster-complete', clusterComplete);
    $(document).on('click', '.order-status, .delete-error', anchorAsynchronous);
    $('.select2entity.submit-asynchronous').on('select2:close', submitAsynchronous);
});

jQuery(function($){
	$.datepicker.regional['fr'] = {
		closeText: 'Fermer',
		prevText: '&#x3c;Préc',
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
    $('.identity-address').toggleClass('hidden');
    if (required) {
        $('.identity-address').find('input').attr('required', 'required');
    } else {
        $('.identity-address').find('input').removeAttr('required');
        $('.identity-address').find('input').val('');
    }
}

function previewFile() {
    const preview = $(this).parent().parent().find('img')[0];
    const [file] = this.files;

    if (file) {
        preview.src = URL.createObjectURL(file)
    }
}

function submitFom() {
    $(this).closest('form').submit()
}

function toggleMenu(e) {
    e.preventDefault();
    $('nav').toggleClass('nav-active');
    $('.nav-bar .btn').toggleClass('nav-hide');
}
function buildSortable() {
    let error = false;
    $("ul.sortable").sortable({
        connectWith: ".connectedSortable",
        helper: "clone",
        cursor: "move",
        zIndex: 99999,
        items: "> li.ui-state-default",
        start: function (event, ui) {
            ui.item.data('old-order', ui.item.index());
        },
        update: function (event, ui) {
            updateLinkOrder(ui.item.data('id'), ui.item.index());
        },
    });
}

function updateLinkOrder(id, newOrder) {
    const sortable = $("ul.sortable").first();
    const parameters = {};
    parameters[sortable.data('parameter')] = id;
    const route = Routing.generate(sortable.data('route'), parameters);
    const data = {'newOrder' : newOrder};
    $.ajax({
        url : route,
        type: 'POST',
        data : data,
    });
}

function getMediaScreen() {
    const width = screen.width;
    let mediaScreen = (width > 800) ? 'md' : 'xs'; 
    document.cookie = "media_screen = "+mediaScreen;
}

function getFile(e) {
    e.preventDefault();
    $inputFile = $('input[type="file"]');
    $inputFile.click();
    $inputFile.on('change',  function(event) {
        filename = event.target.value.split('\\').pop();
        console.log(event.target.value);
        $('#filename').text(filename);
    });
    return false;
}

function modifierEvent() {
    var form = $(this).closest('form');
    var data = {};
    // data[$(this).attr('name')] = $(this).val();
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
          $('#event_container').replaceWith(
            $(html).find('#event_container')
          );
          $('.js-datepicker').datepicker({
            format: 'yyyy-mm-dd hh:ii',
        });
        }
      });
}

function clusterComplete() {
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

    $.ajax({
        url : form.attr('action'),
        type: form.attr('method'),
        data : form.serialize(),
        success: function(html) {
            $(selector).replaceWith($(html).find(selector));
            $('.select2entity').select2entity();
        }
      });
}