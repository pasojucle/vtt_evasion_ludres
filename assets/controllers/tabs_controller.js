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
            const chevronIndex = chevron.dataset.index;
            if (chevronIndex == target) {
                chevron.classList.add('rotate-90');
            } else {
                chevron.classList.remove('rotate-90');
            }
        })
        this.panelTargets.forEach(panel => {
            const panelIndex = panel.dataset.index;
            if (panelIndex == target) {
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        })
    }
}