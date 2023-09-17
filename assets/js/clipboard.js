$(document).ready(function(){
    $(document).on('click', 'a[data-clipboard="1"]', clipboard);
    $(document).on('click', '.email-to-clipboard', emailToClipboard);
});

function clipboard(event) {
    event.preventDefault();
    const value = $(this).attr('href');
    navigator.clipboard.writeText(value);
}

function emailToClipboard(event) {
    event.preventDefault();
    const url = event.target.getAttribute('href');
    fetch(url).then(function (response) {
        return response.json();
    }).then(function (data) {
        console.log(data);
        navigator.clipboard.writeText(data);
    });
}