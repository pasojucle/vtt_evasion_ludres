import { buildContent } from './modal.js';
import { disableScroll, enableScroll } from './toggleScroll.js'
import { hideNav} from './navigation.js'

document.addEventListener('DOMContentLoaded', () => {
    console.log('on load', document.documentElement.clientWidth, window.innerWidth, window.outerWidth);
    document.querySelectorAll('.novelty').forEach((element) => {
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
            const htmlElement = document.createRange().createContextualFragment(json.notifications.list);
            document.querySelector('body').prepend(htmlElement)
            setNavigationsTop();
            document.querySelector('div.dropdown-notifications .tools a').addEventListener('click', toggleNotifications);
        }
    });
}

const setNavigationsTop = () => {
    let vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0)
    console.log('vw', vw);
    if (1024 < vw) {
            const nav = document.querySelector('nav');
        document.querySelector('div.dropdown-notifications').style.top = nav.getBoundingClientRect().top + nav.offsetHeight + 'px';
    }
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
        console.log('json', json);
        if (json.hasNewItem) {
            const element = document.querySelector(`[data-route="${route}"]`);
            element.classList.remove('hidden');
            if (element.parentElement.parentElement.parentElement.classList.contains('nav-sub')) {
                element.closest('li.nav-bar-xs').querySelector('.novelty').classList.remove('hidden');
            }
        }
    });
}

const toggleNotifications = (event) => {
    const notifications = document.querySelector(('div.dropdown-notifications'));
    notifications.classList.toggle('active');
    if (notifications.classList.contains('active')) {
        disableScroll();
        hideNav();
        return;
    }
    enableScroll();
}

export const hideNotifications = () => {
    const notifications = document.querySelector(('div.dropdown-notifications'))
    if (notifications) {
        notifications.classList.remove('active');
    }
}