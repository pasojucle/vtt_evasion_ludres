import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["container"];

    connect() {
        console.log("formModifier")
    }

    async change(event) {
        const form = this.element;
        const data = new FormData(form, event.submitter);
        let containerId = event.target.dataset.containerId;
        if (!containerId && event.target.dataset.controller === "symfony--ux-autocomplete--autocomplete") {
            const parent = event.target.closest("[data-container-id]")

            containerId = parent.dataset.containerId;
        }
        data.append(`${form.name}[handler]`, event.target.name)
        if (event.target.type === 'button') {
            data.append(event.target.name, 1)
        }
        if (this.hasAddToFetchValue) {
            data.append(`${form.name}[${this.addToFetchValue}]`, event.target.dataset[this.addToFetchValue]);
        }
        const response = await fetch(form.action || window.location.href, {
            method: 'POST',
            body: data,
        });

        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        console.log(containerId , this.containerTargets, doc)

        if (containerId) {
            const container = this.containerTargets.find((c) => c.dataset.id === containerId);

            const newContainer = doc.querySelector(`[data-id="${containerId}"]`);
            console.log("container", container, newContainer)
            if (container && newContainer) {
                container.replaceWith(newContainer);
            }
        }
    }
}