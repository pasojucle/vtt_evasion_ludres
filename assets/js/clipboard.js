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
    const value = $(this).attr('href');
    navigator.clipboard.writeText(value);
}

const emailToClipboard = (event) => {
    event.preventDefault();
    const url = event.target.getAttribute('href');
    fetch(url).then ((response) => {
        return response.json();
    }).then((data) => {
        console.log(data);
        navigator.clipboard.writeText(data);
    });
}