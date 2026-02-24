import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { frameId: String }

    refresh() {
        const frame = document.getElementById(this.frameIdValue);
        if (frame) {
            setTimeout(() => {
                frame.reload();
            }, 500);
        }
    }
}