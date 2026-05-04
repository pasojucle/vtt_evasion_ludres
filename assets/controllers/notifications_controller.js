import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["dropdown"];

    static values = {
        url: String,
    }

    connect() {
        this.toggleHandler = this.toggleNotifications.bind(this);
        document.addEventListener("notifications:toggleNotifications", this.toggleHandler);
        this.hideHandler = this.hideNotifications.bind(this);
        document.addEventListener("notifications:hideNotifications", this.hideHandler);
        this.fetchList();
    }

    disconnect() {
        document.removeEventListener("notifications:toggleNotifications", this.toggleHandler);
        document.removeEventListener("notifications:hideNotifications", this.hideHandler);
    }

    async fetchList() {
            console.log("url", this.urlValue)
            await fetch(this.urlValue)
            .then((response) => {
                if (response.status !== 500) {
                    return response.json();
                }
                throw new Error('Something went wrong.');    
            })
            .then((json)=> {
                if (json.modal) {
                    this.dispatch("openWithContent", { 
                        prefix: "modal",
                        detail: { content: json.modal } 
                    })
                }
                console.log("notifications-trigger", json.notifications)
                if (json.notifications && 0 < json.notifications.total) {
                    console.log("notifications-trigger", json.notifications.total)
                    this.dispatch("notify", { 
                        prefix: "notifications-trigger",
                        detail: { total: json.notifications.total } 
                    });
                    this.element.innerHTML = json.notifications.list;

                    this.setDropdownTop();
                }
                if(json.repeat) {
                    setTimeout(() => {
                        this.fetchList();
                    }, 30000);
                }
            });
    }

    setDropdownTop() {
        let vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0)
        if (1024 < vw) {
            const nav = document.querySelector('nav');
            this.dropdownTarget.style.top = nav.getBoundingClientRect().top + nav.offsetHeight + 'px';
        }
    }

    toggleNotifications() {
        console.log("toggleNotifications")
        this.dropdownTarget.classList.toggle('active');
        if (this.dropdownTarget.classList.contains('active')) {
            document.body.classList.add('overflow-hidden', 'touch-none');
            hideNav();
            return;
        }
        this.releaseScroll();
    }

    hideNotifications = () => {
        const notifications = document.querySelector(('div.dropdown-notifications'))
        if (notifications) {
            notifications.classList.remove('active');
        }
    }

    releaseScroll() {
        document.body.classList.remove('overflow-hidden', 'touch-none');
    }

    disconnect() {
        this.releaseScroll();
    }
}
