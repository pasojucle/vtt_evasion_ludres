document.addEventListener("DOMContentLoaded", (event) => {
    getClusters()
});


const getClusters = () => {
    document.querySelectorAll('.cluster-container').forEach((element) => {
        new Cluster(element);
    })
}

class Cluster {
    constructor(clusterEl) {
        this.interval = null;
        this.element = clusterEl;
        this.id = clusterEl.id;
        this.entityId = this.id.replace('cluster-', '');
        this.route = clusterEl.dataset.route;
        this.getSessions();
    }
    getSessions = () => {
        fetch(this.route)
        .then(checkStatus)
        .then(isJsonResponse)
        .then((response) => response.json())
        .then((json) => {
            if (parseInt(json.codeError) === 0) {
                this.replaceCluster(json.html);
            }
        })
        .catch(() => location.replace(location.origin));
    }
    replaceCluster = (text) => {
        const htmlElement = document.createRange().createContextualFragment(text);
        this.element.replaceWith(htmlElement);
        this.init();
        this.clearCluster();
    }
    init = () => {
        this.element = document.getElementById(this.id);
        this.element.querySelectorAll('.admin-session-present').forEach((element) => {
            new Attendance(this, element);
        })
        this.btnComplete = this.element.querySelector('.cluster-complete');
        if (this.btnComplete) {
            this.btnComplete.addEventListener('click', this.complete);
        }
    }
    clearCluster = () => {
        clearInterval(this.interval);
        const self = this;
        this.interval = setInterval(self.getSessions, 60000, self.element)
    }
    complete = (event) => {
        event.preventDefault();
        const route = Routing.generate('admin_cluster_complete', {'cluster': this.entityId});
    
        fetch(route)
        .then(isJsonResponse)
        .then((response) => response.json())
        .then((json)=> {
            this.replaceCluster(json.html);
            const exportButton = document.getElementById(`cluster_export_${this.entityId}`);
            if (exportButton) {
                exportButton.click();
            }
        });
    }
}

class Attendance {
    
    constructor(cluster, btnEl) {
        this.cluster = cluster;
        this.element = btnEl;
        this.session = btnEl.dataset.session;
        this.btnLight = {'btn':'btn-light', 'icon': 'fa-check'};
        this.btnSuccess = {'btn':'btn-success', 'icon': 'fa-check-circle'};

        this.addEventListener();
    }
    addEventListener = () => {
        this.element.addEventListener('click', this.adminSessionPresent);
    }
    adminSessionPresent = (event) => {
        event.preventDefault();
        this.btnEl = (event.tagName === 'A') ? event.target : event.target.closest('a');
        this.btnClass = this.btnEl.classList.contains('btn-success') ? this.btnSuccess : this.btnLight;
        this.iconEl = this.btnEl.querySelector('i');
        this.toggleSessionPresent()
        const data = new FormData();
        data.append('sessionId', this.session);
        this.cluster.clearCluster();
        fetch(Routing.generate('admin_session_present'), {
            method: 'POST',
            body : data,
        })
        .then(checkStatus)
        .catch(() => this.toggleSessionPresent())
    }
    toggleSessionPresent = () => {
        this.newBtnClass = [this.btnSuccess, this.btnLight].find(el => el !== this.btnClass);
        this.element.classList.remove(this.btnClass.btn);
        this.element.classList.add(this.newBtnClass.btn);

        this.iconEl.classList.remove(this.btnClass.icon);
        this.iconEl.classList.add(this.newBtnClass.icon);
        this.refreshTotal();
    }
    refreshTotal = () => {
        const totalEl = this.cluster.element.querySelector('.badge.badge-info');
        let total = parseInt(totalEl.textContent);
        if (this.newBtnClass.btn === 'btn-success') {
            ++total;
        } else {
            --total;
        }
        totalEl.textContent = total;
    }
}

const checkStatus = (response) => {
    if(response.status !== 500) {
         return response;    
    }
    throw new Error('Something went wrong.');    
}

const isJsonResponse = (response) => {
    const contentType = response.headers.get("content-type");
    if (contentType && contentType.indexOf("application/json") !== -1) {
         return response;    
    }
    throw new Error('Something went wrong.');    
}