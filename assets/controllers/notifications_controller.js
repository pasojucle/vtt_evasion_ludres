import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["dropdown"];

    static values = {
        url: String,
    }

    connect() {
        this.timeoutId = null;
        this.toggleHandler = this.toggleNotifications.bind(this);
        document.addEventListener("notifications:toggleNotifications", this.toggleHandler);
        this.hideHandler = this.hideNotifications.bind(this);
        document.addEventListener("notifications:hideNotifications", this.hideHandler);
        this.fetchList();
    }

    disconnect() {
        document.removeEventListener("notifications:toggleNotifications", this.toggleHandler);
        document.removeEventListener("notifications:hideNotifications", this.hideHandler);
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
        }
    }

    async fetchList() {
        if (!this.urlValue) return;

        try {
            const response = await fetch(this.urlValue);

            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }

            const json = await response.json();

            if (!this.element) return;

            if (json.modal) {
                this.dispatch("openWithContent", { 
                    prefix: "modal",
                    detail: { content: json.modal } 
                });
            }

            if (json.notifications && json.notifications.total > 0) {
                this.dispatch("notify", { 
                    prefix: "notifications-trigger",
                    detail: { total: json.notifications.total } 
                });

                this.element.innerHTML = json.notifications.list;

                this.setDropdownTop();
            }

            if (json.repeat) {
                this.timeoutId = setTimeout(() => {
                    this.fetchList();
                }, 30000);
            }

        } catch (error) {
            console.error("Erreur lors de la récupération des notifications:", error);
        }
    }

    setDropdownTop() {
        let vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0)
        if (this.hasDropdownTarget && 1024 < vw) {
            const nav = document.querySelector('nav');
            console.log("dropdownTarget", this.dropdownTarget)
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
