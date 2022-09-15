$(document).ready(function(){
    $(document).on('change', 'input[type="file"]', previewFile);
    $(document).on('click', '.input-file-button', getFile);
});

function previewFile() {
    const previews = $(this).parent().parent().find('img, canvas');
    const [file] = this.files;
    if (file) {
        const image = URL.createObjectURL(file);
        previews.each(function() {
            if (this instanceof HTMLImageElement) {
                this.src = image;
            }
            if (this instanceof HTMLCanvasElement) {
                this.dataset.src =  image;
            }
        });
    }
}

function getFile(e) {
    e.preventDefault();
    const $inputFile = $('input[type="file"]');
    $inputFile.click();
    $inputFile.on('change',  function(event) {
        filename = event.target.value.split('\\').pop();
        $('#filename').text(filename);
    });
    return false;
}