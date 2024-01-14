var intervals = [];
document.addEventListener("DOMContentLoaded", (event) => {
    getClusters()
});

const init = (elementId = null) => {
    const parent = (parent) ? document.getElementById(elementId) : document;
    parent.querySelectorAll('.admin-session-present').forEach((element) => {
        element.addEventListener('click', adminSessionPresent);
    })
    parent.querySelectorAll('.cluster-complete').forEach((element) => {
        element.addEventListener('click', clusterComplete);
    })
}

const getClusters = () => {
    document.querySelectorAll('.cluster-container').forEach((element) => {
        intervals[element.id] = null;
        getCluster(element);
    })
}

const getCluster = (element) => {
    fetch(Routing.generate('admin_cluster_show', {'cluster': element.id.replace('cluster-', '')}))
    .then((response) => response.text())
    .then((text) => {
        replaceCluster(element, text);
    });
}

const replaceCluster = (element, text) => {
    const htmlElement = document.createRange().createContextualFragment(text);
    const clusterEl = document.getElementById(element.id);
    clusterEl.replaceWith(htmlElement);
    init(element.id);
    clearInterval(intervals[element.id]);
    intervals[element.id] = setInterval(getCluster, 60000, element)
} 

const clusterComplete = (event) => {
    event.preventDefault();
    const targetEl = (event.tagName === 'A') ? event.target : event.target.closest('a');
    const parameters = {};
    parameters['cluster'] = targetEl.dataset.clusterId;
    const route = Routing.generate('admin_cluster_complete', parameters);

    fetch(route)
    .then((response) => response.text())
    .then((text)=> {
        const clusterEl = targetEl.closest('.cluster-container'); 
        replaceCluster(clusterEl, text);
        exportButton = document.getElementById(`cluster_export_${parameters['cluster']}`);
        if (exportButton) {
            exportButton.click();
        }
    });
}

const adminSessionPresent = (event) => {
    event.preventDefault();
    const targetEl = (event.tagName === 'a') ? event.target : event.target.closest('a');

    const data = new FormData();
    data.append('sessionId', targetEl.dataset.session);
    fetch(Routing.generate('admin_session_present'), {
        method: 'POST',
        body : data,
    })
    .then((response) => response.json())
    .then((json)=> {
        if (json.codeError === 0) {
            const clusterEl = targetEl.closest('.cluster-container');
            replaceCluster(clusterEl, json.text)
        }
    })
}