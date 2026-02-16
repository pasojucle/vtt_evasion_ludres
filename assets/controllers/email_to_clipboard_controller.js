import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
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