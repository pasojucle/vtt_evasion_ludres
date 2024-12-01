import { addDeleteLink, initAddItemLink } from './entityCollection.js'
import { setNotificationList, hideNotifications, handleShowNotifications } from './notification.js'

document.addEventListener('DOMContentLoaded', () => {
    const modal = buildModal();
    document.querySelector('body').append(modal);
    initModal();
});

export const initModal = () => {
    document.querySelectorAll('a[data-toggle="modal"]').forEach((element) => {
        element.addEventListener('click', handleShowModal);
    });
}

const buildModal = () => {
    const modal = document.createElement('DIV');
    modal.classList.add('modal');
    modal.setAttribute('tabindex', '-1')
    return modal;
}

const handleShowModal = (event) => {
    event.preventDefault();
    const anchor = ( event.target.tagName === 'A') ? event.target : event.target.closest('a');
    var route = anchor.href;
    const modalType = anchor.dataset.type;
    hideNotifications();
    showModal(route, modalType);
}

const handleHideModal = () => {
    document.querySelectorAll('button.close[data-dismiss="modal"]').forEach((element) => {
        element.addEventListener('click', closeModal);
    });
}

export const showModal = async(route, modalType) => {
    await fetch(route, {
        headers: {
            "X-Requested-With": "XMLHttpRequest",
          },
    })
    .then((response) => {
        if (response.status !== 500) {
            return response.text();
        }
        throw new Error('Something went wrong.');    
    })
    .then((text)=> {
        if (0 < text.length) {
            buildContent(text, modalType);
        }
    });
}

export const buildContent = (text, modalType) => {
    openModal(text, modalType);
    $('.js-datepicker').datepicker({
        format: 'yyyy-mm-dd hh:ii',
    });
    initAddItemLink();
    addDeleteLink();
    handleHideModal();
    handleSumbit();
    handleShowNotifications();
}

export const openModal = (text, modalType) => {
    const htmlElement = document.createRange().createContextualFragment(text);
    buildContentModal(htmlElement).then(() => {
        const modal = document.querySelector('.modal');
        const modalHeader = document.querySelector('.modal-header');
        if (modalHeader) {
            modalHeader.classList.add('bg-'+modalType);
        }
        document.querySelectorAll('.modal button:not(button[data-dismiss="modal"])').forEach((element) => {
            element.classList.add('btn-'+modalType);
        });
        setTimeout(() => {
            const modalDialog = document.querySelector('.modal-dialog');
            if (modalDialog) {
                modalDialog.classList.add('modal-open');
            }
        }, 10);
    })
}

const buildContentModal = (htmlElement) => {
    return new Promise((resolve, reject) => {
        const modal = document.querySelector('.modal');
        resolve(modal.replaceWith(htmlElement));
    });
}

export const closeModal = () => {
    document.querySelector('.modal-dialog').classList.remove('modal-open');
    const modal = buildModal();
    setTimeout(function () {
        document.querySelector('.modal').replaceWith(modal);
    }, 500);
}

const handleSumbit = () => {
    document.querySelectorAll('button.btn.async[type="submit"]').forEach((element) => {
        element.addEventListener('click', submitAsync);
    });
}

const submitAsync = async(event) => {
    event.preventDefault();
    const form = event.target.closest('form');
    const data = new FormData(form);
    await fetch(form.action,{
        method: 'POST',
        body : data, 
    })
    .then((response) => response.json())
    .then((json)=> {
        if (parseInt(json.codeError) === 0) {
            closeModal();
            setNotificationList();
        }
    });
}