$(document).ready(function(){
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
      $(document).on('change', '#user_identities_1_otherAddress', updateIdentity);
      $(document).on('change', 'input[type="file"]', previewFile);
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