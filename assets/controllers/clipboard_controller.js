import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
    }

    connect() {
        this.element.addEventListener('click', this.clipboard)
    }

    disconnect() {
        this.element.addEventListener('click', this.clipboard)
    }

    clipboard = (event) => {
        event.preventDefault();
        navigator.clipboard.writeText(this.urlValue);
    }
}