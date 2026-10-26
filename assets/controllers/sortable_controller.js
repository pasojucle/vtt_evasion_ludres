import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    static values = {
        url: String,
        target: String,
        handle: { type: String, default: '.drag-handle' }
    }

    connect() {
        this.sortable = new Sortable(this.element, {
            group: 'shared',
            animation: 150,
            ghostClass: 'sortable-over',
            draggable: '> li[data-sortable="true"]',
            handle: this.handleValue,
            
            touchStartThreshold: 3,
            onEnd: (event) => this.updateOrder(event)
        });
    }

    disconnect() {
        this.sortable.destroy();
    }

    async updateOrder(event) {
        const item = event.item;
        const id = item.dataset.sortableId;
        const csrfToken = item.dataset.csrfToken;
        const newOrder = event.newIndex;
        const url = this.urlValue.replace('0', id);
        const data = new FormData();
        data.append('newOrder', newOrder);
        data.append('csrfToken', csrfToken);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: data,
            });

            const json = await response.json();
            if (!response.ok)  throw new Error(json.data.message ?? 'Erreur de sauvegarde');

        } catch (error) {
            console.error(error);
            if (event.oldIndex !== undefined) {
                const parent = item.parentNode;
                const refNode = parent.children[event.oldIndex > newOrder ? event.oldIndex + 1 : event.oldIndex];
                parent.insertBefore(item, refNode);
            }
        }
    }
}