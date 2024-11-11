import Routing from 'fos-router';

document.addEventListener("DOMContentLoaded", function(event) {
    if(document.getElementById('slideshow-space')) {
        fetchSlideshowDirectorySize();
    }
});

const fetchSlideshowDirectorySize = async() => {
    await fetch(Routing.generate('admin_slideshow_directory_size'),)
    .then((response) => response.json())
    .then((json)=> {
        document.getElementById('slideshow-size').textContent = json.response.text;
        const progress = document.getElementById('slideshow-usage');
        progress.value = json.response.value;
        progress.classList.add(json.response.color)
    });
}