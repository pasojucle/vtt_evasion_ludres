$(document).ready(function(){
    if ($('ul.StepProgress').length > 0) {
        const stepProgressMargingTop = parseInt($('ul.StepProgress').css('margin-top').replace('px', ''));
        const offsetTop = $('ul.StepProgress').offset().top-100;
        let marginTop = stepProgressMargingTop;
        $( window ).scroll(function() {
            if ($(window).scrollTop() > 150) {
                $('nav').addClass('fixed');
                $('nav img').removeClass('hidden');
            } else {
                $('nav').removeClass('fixed');
                $('nav img').addClass('hidden');
            }

            if ($(window).scrollTop() >= offsetTop) {
                console.log($(window).scrollTop() - offsetTop);
                marginTop = $(window).scrollTop() - 200;
            } else {
                marginTop = stepProgressMargingTop;
            }
            $('ul.StepProgress').css('margin-top', marginTop+'px');
        });
    }
    $(document).on('change', '#user_identities_1_otherAddress', updateIdentity);
    $(document).on('change', 'input[type="file"]', previewFile);
    $('.js-datepicker').datepicker({
        format: 'yyyy-mm-dd'
    });
    $(document).on('change', '#event_filter_period', submitFom);
    $(document).on('click', '.nav-bar .btn', toggleMenu);

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
    $('#address_container .form-group-inline').each(function() {
        $(this).toggleClass('hidden');
    });
    $('#address_container input').each(function() {
        if (required) {
            $(this).attr('required', 'required');
        } else {
            $(this).removeAttr('required');
            $(this).val('');
        }
    });
}

function previewFile() {
    const preview = $(this).parent().parent().find('img')[0];
    const [file] = this.files;

    if (file) {
        console.log(URL.createObjectURL(file))
        preview.src = URL.createObjectURL(file)
    }
}

function submitFom() {
    console.log($(this).closest('form'));
    $(this).closest('form').submit()
}

function toggleMenu(e) {
    e.preventDefault();
    $('nav').toggleClass('nav-active');
    $('.nav-bar .btn').toggleClass('nav-hide');
}
