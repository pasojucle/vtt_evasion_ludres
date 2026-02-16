import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { 
        sessionId: Number,
        toggle: String,
        mustProvideRegistration: Boolean,
    }

    connect() {
        this.dispatch("status-changed");
        this.toggleHandler = this.toggle.bind(this);
        window.addEventListener("participation:toggle", this.toggleHandler);
    }

    disconnect() {
        clearInterval(this.interval);
        window.removeEventListener("participation:toggle", this.toggleHandler);
    }

    toggle(event) {
        console.log("toggle", event)
        event.preventDefault();
        const targetId = event.detail?.targetId;
        if (targetId && Number(targetId) !== this.sessionIdValue) {
            return;
        }

        const formData = new FormData();
        formData.append('sessionId', this.sessionIdValue);
        fetch(this.toggleValue, { 
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'text/vnd.turbo-stream.html'
            }})
            .then(response => response.text())
            .then(html => {
                Turbo.renderStreamMessage(html);
            });
    }
}
