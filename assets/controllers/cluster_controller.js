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
        this.exportHandler = this.export.bind(this);
        document.addEventListener("cluster:export", this.exportHandler);

        this.activeHandler = this.activeFromTab.bind(this);
        document.addEventListener("cluster:active-from-tab", this.activeHandler);
    }

    disconnect() {
        clearInterval(this.interval);
        document.removeEventListener("cluster:export", this.exportHandler);
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

    export(event) {
        console.log("export cluster", event);
        window.location.href = this.exportUrlValue;
    }
}