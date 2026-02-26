import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    connect() {
        console.log("filter_controller")
    }

    change(event) {
        console.log('filter_controller', event.target)
        const form = event.target.closest('form');
        if (form) {
            form.requestSubmit();
        }
    }
}