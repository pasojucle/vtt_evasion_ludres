import Routing from 'fos-router';

document.addEventListener("DOMContentLoaded", (event) => {
    document.querySelectorAll('[data-trigger="write-log"]').forEach((element) => {
        element.addEventListener('click', writeLog)
    })
});

const writeLog = async(event) => {
    const element = event.target;
    const data = new FormData(document.querySelector('form[name="log"]'));
    data.append('log[entityId]', element.dataset.entityId);
    await fetch(Routing.generate('log_write'),{
        method: 'POST',
        body : data, 
    });
}