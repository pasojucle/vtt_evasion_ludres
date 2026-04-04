import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static outlets = ["collapse"];

    closeOthers(event) {
        const openedElement = event.target;

        this.collapseOutlets.forEach(outlet => {
            console.log("collapseOutlet", outlet)
            if (outlet.element !== openedElement) {
                outlet.close();
            }
        });
    }
}