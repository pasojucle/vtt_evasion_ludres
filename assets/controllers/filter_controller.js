import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form', "container"];

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

    async updateContent() {
        const form = this.element;
        const formData = new FormData(form);

        const url = new URL(form.action || window.location.href, window.location.origin);
        const redirectTo = new URLSearchParams(url.search).get("_redirect_to");
        if (redirectTo) {
            formData.append("_redirect_to", redirectTo);
        }

        const searchParams = new URLSearchParams(formData);
        url.search = searchParams.toString();

        const target = event.target;
        let containerId = target.dataset.containerId;
        console.log("container", containerId)
        if (!containerId && target.dataset.controller === "symfony--ux-autocomplete--autocomplete") {
            const parent = target.closest("[data-container-id]")

            containerId = parent.dataset.containerId;

        console.log("container parent", parent, containerId)
        }

        const response = await fetch(url, {
            method: 'GET',
        });

        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const container = this.containerTargets.find((c) => c.dataset.id === containerId);

        const newContainer = doc.querySelector(`[data-id="${containerId}"]`);
        console.log("container", this.containerTargets, container, newContainer)
        if (container && newContainer) {
            container.replaceWith(newContainer);
        }
    }
}