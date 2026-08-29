import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["trigger", "chevron", "panel"];

    connect() {
        console.log("tabs_controller")
    }

    switchTab(event) {
        const target = event.currentTarget.dataset.index;
        this.triggerTargets.forEach(trigger => {
            const triggerIndex = trigger.dataset.index;
            if (triggerIndex == target) {
                trigger.classList.replace('bg-slate-200', 'bg-slate-300');
            } else {
                trigger.classList.replace('bg-slate-300', 'bg-slate-200');
            }
        })
        this.chevronTargets.forEach(chevron => {
            const isActive = chevron.dataset.index === target;
            chevron.classList.toggle('rotate-90', isActive);
        })
        this.panelTargets.forEach(panel => {
            const panelIndex = panel.dataset.index;
            const isActive = panel.dataset.index === target;
            panel.classList.toggle('hidden', !isActive);
            this.dispatch("animateFromTab", { 
                prefix: "progress-bar",
                detail: { 
                    wrapper: panel,
                    isActive: isActive,
                }
            });
        })
    }
}