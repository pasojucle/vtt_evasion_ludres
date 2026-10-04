import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { 
        refreshInterval: Number,
        exportUrl: String,
        clusterId: Number,
    }

    static targets = ["countBadge", "btnExport"]

    connect() {
        this.refreshCluster();

        this.activeHandler = this.activeFromTab.bind(this);
        document.addEventListener("cluster:active-from-tab", this.activeHandler);
    }

    disconnect() {
        clearInterval(this.interval);
        document.removeEventListener("cluster:active-from-tab", this.activeHandler);
    }

    activeFromTab(event) {
        const { clusterId, isActive } = event.detail;
        if (Number(clusterId) === this.clusterIdValue) {
            if (isActive) {
                this.reloadCluster();
            } else {
                console.log("clearInterval cluster", this.clusterIdValue);
                clearInterval(this.interval);
            }
        }
    }

    reloadCluster() {
        this.element.reload();
        console.log("reload cluster", this.clusterIdValue);
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

    exportUrlValueChanged(value, previousValue) {
        console.log("watcher", previousValue, value);
        if (previousValue === null && value) {
            window.location.href = value;
        }
    }

    // complete(event) {
    //     event.preventDefault();
    //     fetch(this.completeValue)
    //     .then(response => response.json())
    //     .then(json => {
    //         if (json.modal) {
    //             this.dispatch("openWithContent", { 
    //                 prefix: "modal",
    //                 detail: { content: json.modal } 
    //             });
    //             return;
    //         }

    //         this.reloadCluster();
    //         this.exportCluster();
    //     });
    // }

    // confirmComplete(event) {
    //     event.preventDefault();
    //     const targetId = event.detail?.targetId;
    //     console.log("confirmComplete", this.clusterIdValue, Number(targetId))
    //     if (targetId && Number(targetId) !== this.clusterIdValue) {
    //         return;
    //     }
    //     const data = new FormData();
    //     data.append('isComplete', 1);
    //     fetch(this.completeValue, {
    //         method: 'POST',
    //         body : data,
    //     })
    //     .then(response => {
    //         if (response.ok) {
    //             this.reloadCluster();
    //             this.exportCluster();
    //         }
    //     });
    // }

    // unlock(event) {
    //    event.preventDefault();
    //     fetch(this.completeValue)
    //     .then(response => {
    //         if (response.ok) {
    //             this.reloadCluster();
    //         }
    //     });
    // }

    export() {
        console.log("exportCluster ------------");
        window.location.href = this.exportUrlValue;
    }

    // updateTotal() {
    //     const presents = this.element.querySelectorAll('[data-on-site="1"]').length;
    //     if (this.hasCountBadgeTarget) {
    //         this.countBadgeTarget.textContent = presents;
    //     }
    // }
}