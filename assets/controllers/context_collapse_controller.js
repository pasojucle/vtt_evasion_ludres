import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static outlets = ["collapse", "dropdown"];

    closeOthers(event) {
        const openedElement = event.target;

        const outlets = event.type === "collapse:opened" 
            ? [...this.collapseOutlets, ...this.dropdownOutlets] 
            : this.dropdownOutlets;

        outlets.forEach(outlet => {
            if (outlet.element !== openedElement) {
                outlet.close();
            }
        });
    }
}