document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-toggle="sessionResponse"]').forEach((element) => {
        element.addEventListener('click', handleSessionResponses)
    })
});

const handleSessionResponses = (event) => {
    const element = event.target;
    const target = document.querySelector(element.dataset.target);
    const responsesSelector = 'input[name^="session[responses][surveyResponses]"]';
    if (element.value === 'registered') {
        target.classList.remove('d-none');
        setRequired(responsesSelector, true);
        return
    }
    target.classList.add('d-none');
    setRequired(responsesSelector, false);
}

const setRequired = (selector, value) => {
    document.querySelectorAll(selector).forEach((element) => {
        console.log('response', element)
        element.required = value;
        element.checked = false;
    })
}