import { addDeleteLink, initAddItemLink } from './entityCollection.js'

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('a[data-toggle="modal"]').forEach((element) => {
        element.addEventListener('click', handleShowModal);
    });
    appenddModal().then(() => {
        if (document.querySelector('#notification_list')) {
            callShowModal('#notification_list');
        }
    });
});

const buildModal = () => {
    const modal = document.createElement('DIV');
    modal.classList.add('modal');
    modal.setAttribute('tabindex', '-1')
    return modal;
}

const appenddModal = () => {
    return new Promise((resolve, reject) => {
        const modal = buildModal();
        resolve(document.querySelector('body').append(modal));
    });
}

const handleShowModal = (event) => {
    event.preventDefault();
    var route = event.target.href;
    const modalType = event.target.dataset.type;
    showModal(route, modalType);
}

const handleHideModal = () => {
    document.querySelectorAll('button.close[data-dismiss="modal"]').forEach((element) => {
        element.addEventListener('click', closeModal);
    });
}

function callShowModal(target) {
    const targetEl = document.querySelector(target)
    var route = targetEl.href;
    const modalType = targetEl.dataset.type
    showModal(route, modalType);
}

const showModal = async(route, modalType) => {
    await fetch(route)
    .then((response) => {
        if (response.status !== 500) {
            return response.text();
        }
        throw new Error('Something went wrong.');    
    })
    .then((text)=> {
        if (0 < text.length) {
            openModal(text, modalType);
            $('.js-datepicker').datepicker({
                format: 'yyyy-mm-dd hh:ii',
            });
            initAddItemLink();
            addDeleteLink();
            handleHideModal();
        }
    });
}

export const openModal = (text, modalType) => {
    const htmlElement = document.createRange().createContextualFragment(text);
    console.log('htmlElement', text)
    buildContentModal(htmlElement).then(() => {
        document.querySelector('.modal-header').classList.add('bg-'+modalType);
        document.querySelectorAll('.modal button:not(button[data-dismiss="modal"])').forEach((element) => {
            element.classList.add('btn-'+modalType);
        });
        setTimeout(() => {
            document.querySelector('.modal-dialog').classList.add('modal-open');
        }, 10);
    })
}

const buildContentModal = (htmlElement) => {
    return new Promise((resolve, reject) => {
        const modal = document.querySelector('.modal');
        resolve(modal.replaceWith(htmlElement));
    });
}

export function closeModal() {
    document.querySelector('.modal-dialog').classList.remove('modal-open');
    const modal = buildModal();
    setTimeout(function () {
        document.querySelector('.modal').replaceWith(modal);
    }, 500);
}