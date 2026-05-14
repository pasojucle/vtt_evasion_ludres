import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    connect() {
        console.log("filter_controller")
    }

    change(event) {
        console.log('filter_controller change', event.target)
        const form = event.target.closest('form');
        if (form) {
            // On désactive temporairement les champs vides avant la soumission
            // pour nettoyer l'ur des champs vides
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.value === "") {
                    input.disabled = true;
                }
            });
            form.requestSubmit();
            setTimeout(() => {
                inputs.forEach(input => input.disabled = false);
            }, 100);
        }
    }
}