import '../styles/dashboard.css';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.dashboard-item[data-route]').forEach(element => {
        fetchData(element)
    });
}, false);

const fetchData = async(element) => {
    await fetch(Routing.generate(element.dataset.route), )
        .then((response) => response.text())
        .then((text)=> {
            const htmlElement = document.createRange().createContextualFragment(text);
            element.replaceWith(htmlElement);
        });
}
