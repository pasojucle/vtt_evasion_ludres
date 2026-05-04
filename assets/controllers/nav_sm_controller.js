import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['container', 'trigger'];

   toggle(event) {
        console.log("nav toggle", this.containerTarget, this.triggerTarget)
        event.preventDefault();
        const nav = this.containerTarget;
        nav.classList.toggle('nav-active');
        const trigger = this.triggerTarget;
        console.log("trigger", trigger)
        trigger.classList.toggle('nav-hide');
        
        if (nav.classList.contains('nav-active')) {
            this.dispatch("hideNotifications", { 
                prefix: "notifications",
            });
            document.body.classList.add('overflow-hidden', 'touch-none');
            return;
        }
        this.releaseScroll();
    }

    hideNav() {
        document.querySelector('nav').classList.remove('nav-active')
        document.querySelector('.nav-bar .btn').classList.remove('nav-hide');
        closeDropdown();
    }

    releaseScroll() {
        document.body.classList.remove('overflow-hidden', 'touch-none');
    }

    disconnect() {
        this.releaseScroll();
    }
}