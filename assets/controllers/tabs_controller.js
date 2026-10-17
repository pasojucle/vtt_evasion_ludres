import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["trigger", "chevron", "panel"];

    connect() {
        this.element.addEventListener('invalid', this.handleInvalidField.bind(this), true);
    }

    disconnect() {
        this.element.removeEventListener('invalid', this.handleInvalidField.bind(this), true);
    }

    switchTab(event) {
        const targetIndex = event.currentTarget.dataset.index;
        this.switchTabFromTargetIndex(targetIndex)
    }

    switchTabFromTargetIndex(targetIndex) {
        this.triggerTargets.forEach(trigger => {
            const triggerIndex = trigger.dataset.index;
            if (triggerIndex == targetIndex) {
                trigger.classList.replace('bg-slate-200', 'bg-slate-300');
            } else {
                trigger.classList.replace('bg-slate-300', 'bg-slate-200');
            }
        })
        this.chevronTargets.forEach(chevron => {
            const isActive = chevron.dataset.index === targetIndex;
            chevron.classList.toggle('rotate-90', isActive);
        })
        this.panelTargets.forEach(panel => {
            const panelIndex = panel.dataset.index;
            const isActive = panel.dataset.index === targetIndex;
            panel.classList.toggle('hidden', !isActive);
            this.dispatch("animate-from-tab", {
                target: panel,
                prefix: "progress-bar",
                detail: { 
                    wrapper: panel,
                    isActive: isActive,
                }
            });

            const cluster = panel.querySelector("[data-controller=\"cluster\"]");
            if (cluster) {
                this.dispatch("active-from-tab", {
                    prefix: "cluster",
                    detail: { 
                        clusterId: cluster.id.replace("cluster-", ""),
                        isActive: isActive,
                    }
                });
            }
        })
    }

    handleInvalidField(event) {
        if (this.alreadyHandled) return;
        this.alreadyHandled = true;

        const handleInvalidField = event.target;
        if (!handleInvalidField) {
            return;
        }
        const targetPanel = handleInvalidField.closest('[data-tabs-target="panel"]');
        console.log("tabPanel", targetPanel)
        if (targetPanel) {
            this.switchTabFromTargetIndex(targetPanel.dataset.index);
        }

        setTimeout(() => {
            this.alreadyHandled = false;
        }, 300);
    }
}