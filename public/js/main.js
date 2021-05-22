$(document).ready(function(){
    $( window ).scroll(function() {
        if ($(window).scrollTop() > 150) {
            $('nav').addClass('fixed');
            $('nav img').removeClass('hidden');
        } else {
            $('nav').removeClass('fixed');
            $('nav img').addClass('hidden');
        }
      });
  });