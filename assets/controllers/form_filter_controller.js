import { Controller } from '@hotwired/stimulus';

// Cottroller à utiliser pour les filtre en GET
// Turbo interceptera l'événement car le formulaire a data-turbo-frame
export default class extends Controller {
    submit() {
        this.element.requestSubmit();
    }
}