import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
    }

    connect() {
        this.element.addEventListener('click', this.emailToClipboard)
    }

    disconnect() {
        this.emailToClipboardTarget.removeEventListener('click', this.emailToClipboard)
    }

    emailToClipboard = (event) => {
        event.preventDefault();
        const url = this.urlValue;
        fetch(url).then ((response) => {
            return response.json();
        }).then((data) => {
            navigator.clipboard.writeText(data);
        });
    }
}