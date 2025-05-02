document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('a[data-clipboard="1"]').forEach((element) => {
        element.addEventListener('click', clipboard)
    })
    document.querySelectorAll('.email-to-clipboard').forEach((element) => {
        element.addEventListener('click', emailToClipboard)
    })
   
});
const clipboard = (event) => {
    event.preventDefault();
    const value = event.target.href;
    navigator.clipboard.writeText(value);
}

export const emailToClipboard = (event) => {
    event.preventDefault();
    const url = event.target.getAttribute('href');
    fetch(url).then ((response) => {
        return response.json();
    }).then((data) => {
        navigator.clipboard.writeText(data);
    });
}