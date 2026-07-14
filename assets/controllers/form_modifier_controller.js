import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["container"];

    connect() {
        console.log("formModifier")
    }

    async change(event) {
        const form = this.element;
        const data = new FormData(form, event.submitter);
        const target = event.target;
        const addToFetch = target.dataset.addToFetch;
        let containerId = target.dataset.containerId;
        if (!containerId && target.dataset.controller === "symfony--ux-autocomplete--autocomplete") {
            const parent = target.closest("[data-container-id]")

            containerId = parent.dataset.containerId;
        }
        data.append(`${form.name}[handler]`, target.name)
        if (target.type === 'button') {
            data.append(target.name, 1)
        }
        if (addToFetch) {
            data.append(`${form.name}[${addToFetch}]`, target.dataset[addToFetch]);
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
            containerId.split(';').forEach(targetId => {
                const container = this.containerTargets.find((c) => c.dataset.id === targetId);

                const newContainer = doc.querySelector(`[data-id="${targetId}"]`);
                console.log("container", container, newContainer)
                if (container && newContainer) {
                    container.replaceWith(newContainer);
                }
            })
        }
    }
}