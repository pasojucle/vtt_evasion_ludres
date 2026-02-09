import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    static values = {
        route: String,
        target: String
    }

    connect() {
        this.sortable = new Sortable(this.element, {
            group: 'shared',
            animation: 150,
            ghostClass: 'sortable-over',
            draggable: '> li.sortable-state-default',
            onEnd: (event) => this.updateOrder(event)
        });
    }

    disconnect() {
        this.sortable.destroy();
    }

    async updateOrder(event) {
        const item = event.item;
        const id = item.dataset.id;
        const newOrder = event.newIndex;
        const url = this.routeValue.replace('0', id);

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 'newOrder': newOrder })
            });

            if (!response.ok) throw new Error('Erreur de sauvegarde');
        } catch (error) {
            console.error(error);
        }
    }
}