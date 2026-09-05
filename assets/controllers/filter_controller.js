import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form'];

    submit() {
        if (this.hasFormTarget) {
            // On désactive temporairement les champs vides avant la soumission
            // pour nettoyer l'ur des champs vides
            const inputs = this.formTarget.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.value === "") {
                    input.disabled = true;
                }
            });
            const frame = this.element.closest('[data-with-skeleton="true"]');
            if (frame) {
                frame.setAttribute("busy", "");
            }
            this.formTarget.requestSubmit();
            setTimeout(() => {
                inputs.forEach(input => input.disabled = false);
            }, 100);
        }
    }
}