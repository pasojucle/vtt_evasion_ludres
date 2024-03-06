import { addDeleteLink, initAddItemLink } from './entityCollection.js'

$(function () {
    $('body').append('<div class="modal" tabindex="-1"></div>');
    $(document).on('click', 'a[data-toggle="modal"]', handleShowModal);
    $(document).on('click', 'button.close[data-dismiss="modal"]', closeModal);

    console.log('modal window', document.querySelector('#modal_window_show'));
    if (document.querySelector('#modal_window_show')) {
        callShowModal('#modal_window_show');
    }

});

function handleShowModal(event) {
    event.preventDefault();
    var route = $(this).attr("href");
    const modalType = $(this).data("type")
    showModal(route, modalType);
}

function callShowModal(target) {
    var route = $(target).attr("href");
    const modalType = $(target).data("type")
    showModal(route, modalType);
}

function showModal(route, modalType) {
    $.ajax({
        url: route,
        type: "get",
        success: function (html, textStatus, xhr) {
            if (204 !== xhr.status) {
                openModal(html, modalType);
                $('.js-datepicker').datepicker({
                    format: 'yyyy-mm-dd hh:ii',
                });
                initAddItemLink();
                addDeleteLink();
                setTimeout(function () {
                    //$('.modal-dialog').transition({ top: 100px });
                    $('.modal-dialog').addClass('modal-open');
                }, 100);
            }
        }
    });
}

export function openModal(text, modalType) {
    $('.modal').replaceWith($(text));
    $('.modal').find('.modal-header').addClass('bg-'+modalType);
    $('.modal').find('button:not(button[data-dismiss="modal"])').addClass('btn-'+modalType);
    // const htmlElement = document.createRange().createContextualFragment(text);
    // document.querySelector('.modal').replaceWith(htmlElement);
    // document.querySelector('.modal .modal-header').classList.add('bg-'+modalType);
    // document.querySelectorAll('.modal button:not(button[data-dismiss="modal"])').forEach((element) => {
    //     element.classList.add('btn-'+modalType);
    // })
    setTimeout(function () {
        document.querySelector('.modal .modal-dialog').classList.add('modal-open');
    }, 100);
}

export function closeModal() {
    $('.modal-dialog').removeClass('modal-open');
    let html = document.createElement("div");
    $(html).addClass('modal').attr('tabindex', -1);
    setTimeout(function () {
        $('.modal').replaceWith($(html));
    }, 500);
}