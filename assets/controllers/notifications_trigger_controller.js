import { Controller } from '@hotwired/stimulus';


export default class extends Controller {
    static targets = ["trigger", "badge"];

    connect() {
        this.notifyHandler = this.notify.bind(this);
        document.addEventListener("notifications-trigger:notify", this.notifyHandler);
    }

    disconnect() {
        document.removeEventListener("notifications-trigger:notify", this.notifyHandler);
    }

    notify(event) {
        const { total } = event.detail;
        console.log("notify notifications-trigger")
        this.triggerTargets.forEach((trigger) => {
            trigger.classList.remove('hidden');
        });
        this.badgeTargets.forEach((badge) => {
            badge.textContent = total
        });

    }

    toggleList() {
        console.log("toggle list")
        this.dispatch("toggleNotifications", { 
            prefix: "notifications",
        });
    }
}
