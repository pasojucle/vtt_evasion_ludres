document.addEventListener("DOMContentLoaded", function(event) {
    const securityEl = document.querySelector('.security');
    if (securityEl) {
        if (document.hidden !== undefined) { // Opera 12.10 and Firefox 18 and later support
        hidden = "hidden";
        visibilityChange = "visibilitychange";
        } else if (document.mozHidden !== undefined) {
        hidden = "mozHidden";
        visibilityChange = "mozvisibilitychange";
        } else if (document.msHidden !== undefined) {
        hidden = "msHidden";
        visibilityChange = "msvisibilitychange";
        } else if (document.webkitHidden !== undefined) {
        hidden = "webkitHidden";
        visibilityChange = "webkitvisibilitychange";
        } else if (document.oHidden !== undefined) {
        hidden = "oHidden";
        visibilityChange = "ovisibilitychange";
        }

        document.addEventListener(visibilityChange, function(event) {
            isGranted(securityEl.dataset.role);
        }, false);
    }
});


const isGranted = async(role) => {
    await fetch(Routing.generate('is_granted_by_role', {'role': role}), )
        .then((response) => response.json())
        .then((json)=> {
            if (!json.isGranted) {
                location.replace(location.origin)
            }
        });
}