import { Controller } from '@hotwired/stimulus';
import { disableScroll, enableScroll } from '../utils/toggleScroll.js';


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
            disableScroll();
            return;
        }
        enableScroll();
    }

    hideNav() {
        document.querySelector('nav').classList.remove('nav-active')
        document.querySelector('.nav-bar .btn').classList.remove('nav-hide');
        closeDropdown();
    }
}