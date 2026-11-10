import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
    }

    emailToClipboard = async(event) => {
        event.preventDefault();
        const url = this.urlValue;
        const response = await fetch(url);

        const json = await response.json();
        if (json.emails) {
            navigator.clipboard.writeText(json.emails);
        }
    }
}