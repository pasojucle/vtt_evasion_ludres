import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        console.log("flash_controller")
        this.show();
    }

    show() {
        console.log("show flash")
        setTimeout(() => {
            this.element.classList.remove('translate-y-8', 'opacity-0');
            this.element.classList.add('translate-y-0', 'opacity-100');
        }, 50);

        this.hide()
    }

    hide() {
        console.log("hide flash")
        setTimeout(() => {
            this.element.classList.remove('translate-y-0', 'opacity-100');
            this.element.classList.add('translate-y-4', 'opacity-0');
        }, 3000);

        setTimeout(() => {
            this.element.remove();
        }, 6000);
    }
}