import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["group", "content", "chevron"];

    toggle(event) {
        event.preventDefault();
        const activeGroup = event.target.closest("[data-group]")?.dataset.group;
        this.contentTargets.forEach((content) => {
            const group = content.closest("[data-group]");
            if (group.dataset.group !== activeGroup) {
                content.classList.replace("down", "up")
            } else {
                content.classList.replace("up", "down")
            }
        })
        this.chevronTargets.forEach((chevron) => {
            const group = chevron.closest("[data-group]");
            if (group?.dataset.group === activeGroup) {
                chevron.classList.replace("rotate-0", "rotate-90")
            } else {
                chevron.classList.replace("rotate-90", "rotate-0")
            }
        })

        this.setCookie('admin_nav_actived', activeGroup, 30);
    }

    setCookie(cName, cValue, expDays) {
        let date = new Date();
        date.setTime(date.getTime() + (expDays * 24 * 60 * 60 * 1000));
        const expires = "expires=" + date.toUTCString();
        document.cookie = cName + "=" + cValue + "; " + expires + "; path=/; url=" + location.hostname;
    }
}