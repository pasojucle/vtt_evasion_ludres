document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.check-toggle').forEach(element => {
        element.addEventListener('change', handleCheckChange);
        formToggle(element);
    })
});

const handleCheckChange = (event) => {
    formToggle(event.target);
}

const formToggle = (target) => {
    const form = target.closest('form');
    Object.entries(form.elements).forEach(([key, element]) => {
        if (element.type !== 'checkbox') {
            if (target.checked) {
                element.removeAttribute('disabled');
            } else {
                element.setAttribute('disabled', "");
            } 
        }
    })
}
