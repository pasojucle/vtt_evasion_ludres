import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["content", "trigger", "chevron"];

    toggle(event) {
        event.preventDefault();
        if (this.contentTarget.classList.contains('up')) {
            this.open();
        } else {
            this.close();
        }
    }

    open() {
        this.dispatch("opened", { bubbles: true });
        if (this.hasTriggerTarget && this.triggerTarget.classList.contains("bg-background")) {
            this.triggerTarget.classList.add("bg-primary", "text-white")
            this.triggerTarget.classList.remove("bg-background")
        }
        if (this.hasContentTarget) {
            this.contentTarget.classList.replace("up", "down")
        }
        if (this.hasChevronTarget) {
            this.chevronTarget.classList.replace("rotate-0", "rotate-90")
        }
        if (this.element.dataset.group) {
            this.setCookie('admin_nav_actived', this.element.dataset.group, 30);
        }
    }

    close() {
        if (this.hasTriggerTarget && this.triggerTarget.classList.contains("bg-primary")) {
            this.triggerTarget.classList.remove("bg-primary", "text-white")
            this.triggerTarget.classList.add("bg-background")
        }
        if (this.hasContentTarget) {
            this.contentTarget.classList.replace("down", "up")
        }
        if (this.hasChevronTarget) {
            this.chevronTarget.classList.replace("rotate-90", "rotate-0")
        }
    }

    setCookie(cName, cValue, expDays) {
        let date = new Date();
        date.setTime(date.getTime() + (expDays * 24 * 60 * 60 * 1000));
        const expires = "expires=" + date.toUTCString();
        document.cookie = cName + "=" + cValue + "; " + expires + "; path=/; url=" + location.hostname;
    }
}