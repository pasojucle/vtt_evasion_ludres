import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["content", "trigger", "icon", "activeIcon"];

    toggle(event) {
        event.preventDefault();
        if (this.contentTarget.classList.contains('hidden')) {
            this.open();
        } else {
            this.close();
        }
    }

    open() {
        this.dispatch("opened", { bubbles: true });
        if (this.hasTriggerTarget && this.triggerTarget.classList.contains("bg-transparent")) {
            this.triggerTarget.classList.replace("bg-transparent", "bg-neutral-300");
            this.triggerTarget.classList.remove("group");
        }
        if (this.hasContentTarget) {
            this.contentTarget.classList.replace("hidden", "block");

            const triggerRect = this.triggerTarget.getBoundingClientRect();
            const contentHeight = this.contentTarget.offsetHeight;
            const spaceBelow = window.innerHeight - triggerRect.bottom;
            this.contentTarget.classList.remove("top-0", "bottom-0");
            if (spaceBelow < contentHeight) {
                this.contentTarget.classList.add("bottom-0");
            } else {
                this.contentTarget.classList.add("top-0");
            }
        }
        if (this.hasActiveIconTarget) {
            this.activeIconTarget.classList.remove("hidden");
        }
        if (this.hasIconTarget) {
            this.iconTarget.classList.add("hidden");
        }
    }

    close() {
        if (this.hasTriggerTarget && this.triggerTarget.classList.contains("bg-neutral-300")) {
            this.triggerTarget.classList.replace("bg-neutral-300", "bg-transparent");
            this.triggerTarget.classList.add("group");
        }
        if (this.hasContentTarget) {
            this.contentTarget.classList.replace("block", "hidden");
        }
        if (this.hasIconTarget) {
            this.iconTarget.classList.remove("hidden");
        }
        if (this.hasActiveIconTarget) {
            this.activeIconTarget.classList.add("hidden");
        }
    }
}