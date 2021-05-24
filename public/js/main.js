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
  });