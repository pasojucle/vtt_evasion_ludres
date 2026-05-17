document.addEventListener("turbo:before-fetch-request", (event) => {
    const initiator = event.target;
    if (initiator.tagName !== "FORM") {
        return;
    }
    const activeFrame = document.querySelector('turbo-frame[data-with-skeleton="true"]');
    if (activeFrame) {
        activeFrame.setAttribute("busy", "");
    }
});

const clearSkeletons = () => {
    document.querySelectorAll('turbo-frame[busy]').forEach(frame => {
        frame.removeAttribute("busy");
    });
};

document.addEventListener("turbo:load", clearSkeletons);
document.addEventListener("turbo:visit", clearSkeletons);