import { initModal, buildContent, closeModal } from './modal.js';
import { disableScroll, enableScroll } from './toggleScroll.js';
import { hideNav} from './navigation.js';
import Routing from 'fos-router';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.novelty').forEach((element) => {
        if (element.dataset.route !== undefined) {
            hasNews(element.dataset.route)
        }
    })
    modalBuilded().then(() => {
        setNotificationList();
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

export const setNotificationList = async() => {
    const notificationList = document.querySelector('a[data-toggle="notification"]');
    if (notificationList) {
        var route = notificationList.href;
        const modalType = notificationList.dataset.type
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
            if (json.notifications && 0 < json.notifications.total) {
                document.querySelectorAll('.bell-notifications').forEach((element) => {
                    element.classList.remove('d-none');
                    element.querySelector('.badge.notifications').textContent = json.notifications.total
                });
                const htmlElement = document.createRange().createContextualFragment(json.notifications.list);
                document.querySelector('body').prepend(htmlElement)
                setNavigationsTop();
                document.querySelector('div.dropdown-notifications .tools a').addEventListener('click', toggleNotifications);
                initModal();
            }
            if(json.repeat) {
                setTimeout(() => {
                    setNotificationList();
                }, 30000);
            }
        });
    }
}

const setNavigationsTop = () => {
    let vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0)
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
        if (json.hasNewItem) {
            const element = document.querySelector(`[data-route="${route}"]`);
            element.classList.remove('hidden');
            if (element.parentElement.parentElement.parentElement.classList.contains('nav-sub')) {
                element.closest('li.nav-bar-xs').querySelector('.novelty').classList.remove('hidden');
            }
        }
    });
}

const toggleNotifications = () => {
    const notifications = document.querySelector(('div.dropdown-notifications'));
    notifications.classList.toggle('active');
    if (notifications.classList.contains('active')) {
        disableScroll();
        hideNav();
        return;
    }
    enableScroll();
}

const toggleNotificationsFromModal = (event) => {
    event.preventDefault();
    toggleNotifications();
    closeModal();
}

export const hideNotifications = () => {
    const notifications = document.querySelector(('div.dropdown-notifications'))
    if (notifications) {
        notifications.classList.remove('active');
    }
}

export const handleShowNotifications = () => {
    const anchor = document.querySelector('.modal a[data-toggle="notifications"]')
    if (anchor) {
        anchor.addEventListener('click', toggleNotificationsFromModal)
    }
}