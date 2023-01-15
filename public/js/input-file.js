$(document).ready(function(){
    $(document).on('change', 'input[type="file"]', previewFile);
    $(document).on('click', '.input-file-button', getFile);
});

function previewFile() {
    const previews = $(this).parent().parent().find('img, canvas, object');
    const [file] = this.files;
    if (file) {
        const image = URL.createObjectURL(file);
        previews.each(function() {
            this.classList.add('hidden');
            if (this instanceof HTMLImageElement && file.type.includes('image')) {
                this.src = image;
                this.classList.remove('hidden');
            }
            if (this instanceof HTMLCanvasElement && file.type.includes('image')) {
                this.dataset.src =  image;
                this.classList.remove('hidden');
            }
            if (this instanceof HTMLObjectElement && 'application/pdf' === file.type) {
                this.classList.remove('hidden');
                this.data =  image;
            }
        });
    }
}

function getFile(e) {
    e.preventDefault();
    const $inputFile = $('input[type="file"]');
    $inputFile.click();
    $inputFile.on('change',  function(event) {
        let filename = event.target.value.split('\\').pop();
        $('#filename').text(filename);
    });
    return false;
}