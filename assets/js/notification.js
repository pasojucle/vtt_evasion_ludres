import { buildContent } from './modal.js'

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.badge.novelty').forEach((element) => {
        if (element.dataset.route !== undefined) {
            hasNews(element.dataset.route)
        }
    })
    modalBuilded().then(() => {
        if (document.querySelector('#notification_list')) {
            callShowModal('#notification_list');
        }
    });
    document.querySelectorAll('.badge.notifications').forEach((element) => {
        element.parentElement.addEventListener('click', toggleNotifications);
    });
});

const modalBuilded = () => {
    return new Promise((resolve, reject) => {
        resolve(document.querySelector('.modal'));
    });
}

const callShowModal = async(target) => {
    const targetEl = document.querySelector(target)
    var route = targetEl.href;
    const modalType = targetEl.dataset.type
    await fetch(route, {
        headers: {
            "X-Requested-With": "XMLHttpRequest",
          },
    })
    .then((response) => {
        if (response.status !== 500) {
            return response.json();
        }
        throw new Error('Something went wrong.');    
    })
    .then((json)=> {
        if (json.modal) {
            buildContent(json.modal, modalType);
        }
        if (0 < json.notifications.total) {
            document.querySelectorAll('.bell-notifications').forEach((element) => {
                element.classList.remove('d-none');
                element.querySelector('.badge.notifications').textContent = json.notifications.total
            });
            console.log('body width', document.querySelector('body').offsetWidth)
            document.querySelector('.nav-bar').style.width = document.querySelector('body').offsetWidth+'px';
            const htmlElement = document.createRange().createContextualFragment(json.notifications.list);
            document.querySelector('body').append(htmlElement)
            document.querySelector('div.dropdown-notifications .tools a').addEventListener('click', toggleNotifications);
        }
    });
}

const hasNews = async(route) => {
    await fetch( Routing.generate(route))
    .then((response) => {
        if (response.status !== 500) {
            return response.json();
        }
        throw new Error('Something went wrong.');    
    })
    .then((json)=> {
        if (json.hasNewItem) {
            const element = document.querySelector(`[data-route="${route}"]`);
            element.classList.remove('hidden');
            if (element.parentElement.classList.contains('nav-sub')) {
                element.closest('li.nav-bar-xs').querySelector('.badge.novelty').classList.remove('hidden');
            }
        }
    });
}

const toggleNotifications = () => {
    document.querySelector(('div.dropdown-notifications')).classList.toggle('active');
}