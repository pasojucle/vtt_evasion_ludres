import { Controller } from '@hotwired/stimulus';
import { disableScroll, enableScroll } from '../js/toggleScroll.js';
import { hideNav} from '../js/navigation.js';

export default class extends Controller {
    static targets = ["dropdown"];

    static values = {
        url: String,
    }

    connect() {
        this.toggleHandler = this.toggleNotifications.bind(this);
        document.addEventListener("notifications:toggleNotifications", this.toggleHandler);
        this.fetchList();
    }

    disconnect() {
        document.removeEventListener("notifications:toggleNotifications", this.toggleHandler);
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
            document.querySelector('div.dropdown-notifications').style.top = nav.getBoundingClientRect().top + nav.offsetHeight + 'px';
        }
    }

    toggleNotifications() {
        console.log("toggleNotifications")
        this.dropdownTarget.classList.toggle('active');
        if (this.dropdownTarget.classList.contains('active')) {
            disableScroll();
            hideNav();
            return;
        }
        enableScroll();
    }
}
