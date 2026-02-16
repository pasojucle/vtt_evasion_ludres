import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { 
        refreshInterval: Number,
        complete: String,
        unlock: String,
        export: String,
        clusterId: Number,
    }

    static targets = ["countBadge", "btnExport"]

    connect() {
        this.refreshCluster();
        this.confirmHandler = this.confirmComplete.bind(this);
        window.addEventListener("cluster:confirm-complete", this.confirmHandler);
    }

    reloadCluster() {
        this.element.reload();
        this.refreshCluster();
    }

    refreshCluster() {
        clearInterval(this.interval);
        if (this.refreshIntervalValue > 0) {
            this.interval = setInterval(() => {
                this.element.reload()
            }, this.refreshIntervalValue);
        }
    }

    disconnect() {
        clearInterval(this.interval);
        window.removeEventListener("cluster:confirm-complete", this.confirmHandler);
    }

    complete(event) {
        event.preventDefault();
        fetch(this.completeValue)
        .then(response => response.json())
        .then(json => {
            if (json.modal) {
                this.dispatch("openWithContent", { 
                    prefix: "modal",
                    detail: { content: json.modal } 
                });
                return;
            }

            this.reloadCluster();
            this.exportCluster();
        });
    }

    confirmComplete(event) {
        event.preventDefault();
        const targetId = event.detail?.targetId;
        console.log("confirmComplete", this.clusterIdValue, Number(targetId))
        if (targetId && Number(targetId) !== this.clusterIdValue) {
            return;
        }
        const data = new FormData();
        data.append('isComplete', 1);
        fetch(this.completeValue, {
            method: 'POST',
            body : data,
        })
        .then(response => {
            if (response.ok) {
                this.reloadCluster();
                this.exportCluster();
            }
        });
    }

    unlock(event) {
       event.preventDefault();
        fetch(this.completeValue)
        .then(response => {
            if (response.ok) {
                this.reloadCluster();
            }
        });
    }

    exportCluster(event=null) {
        if (event) {
            event.preventDefault();
        }
        window.location.href = this.exportValue;
    }

    updateTotal() {
        const presents = this.element.querySelectorAll('.btn-success[data-controller="participation"]').length;
        if (this.hasCountBadgeTarget) {
            this.countBadgeTarget.textContent = presents;
        }
    }
}