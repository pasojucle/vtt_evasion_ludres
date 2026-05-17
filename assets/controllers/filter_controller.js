import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form'];

    connect() {
        console.log("filter_controller", this.hasFormTarget, this.formTarget)
    }

    submit() {
        console.log('filter_controller change')
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
            console.log("frame", frame);
            if (frame) {
                frame.setAttribute("busy", "");
            }
            this.formTarget.requestSubmit();
            setTimeout(() => {
                inputs.forEach(input => input.disabled = false);
            }, 100);
        }
    }

    clear(event) {
        const name = event.currentTarget.dataset.filterName;
        const input = this.element.querySelector(`[name="${name}"]`);
        console.log('filter_controller clear', input)

        if (input) {
            input.value = "";
            this.submit(event);
        }
    }
}