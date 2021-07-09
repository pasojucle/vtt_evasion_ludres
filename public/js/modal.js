$(function () {
    $('body').append('<div class="modal" tabindex="-1"></div>');
    $(document).on('click', 'a[data-toggle="modal"]', showModal);
    $(document).on('click', 'button.close[data-dismiss="modal"]', closeModal);
});

function showModal(event) {
    event.preventDefault();
    var route = $(this).attr("href");
    const modalType = $(this).data("type")
    $.ajax({
        url: route,
        type: "get",
        success: function (html) {
            $('.modal').replaceWith($(html));
            $('.modal').find('.modal-header').addClass('bg-'+modalType);
            $('.modal').find('button:not(button[data-dismiss="modal"])').addClass('btn-'+modalType);
            setTimeout(function () {
                //$('.modal-dialog').transition({ top: 100px });
                $('.modal-dialog').addClass('modal-open');
            }, 100);
        }
    });
}

function closeModal() {
    $('.modal-dialog').removeClass('modal-open');
    let html = document.createElement("div");
    $(html).addClass('modal').attr('tabindex', -1);
    setTimeout(function () {
        $('.modal').replaceWith($(html));
    }, 500);
}