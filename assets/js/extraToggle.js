document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-toggle="sessionResponse"]').forEach((element) => {
        console.log('element', element)
        element.addEventListener('click', toggleSessionResponses)
    })
});

const toggleSessionResponses = (event) => {
    const element = event.target;
    const target = document.querySelector(element.dataset.target);
    const responsesSelector = 'input[name^="session[responses][surveyResponses]"]';
    console.log('availability', element, target)
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