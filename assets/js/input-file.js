// document.addEventListener("DOMContentLoaded", () => {
//     initInputFile();
// });

// export const initInputFile = () => {
//     document.querySelectorAll('input[type="file"].input-file').forEach((element) => {
//         element.addEventListener('change', previewFile);
//     })
//     document.querySelectorAll('.input-file-button').forEach((element) => {
//         element.addEventListener('click', getFile);
//     })
// }


export const previewFile = (event) => {
    const previews = event.currentTarget.parentElement.parentElement.querySelectorAll('img, canvas, object, video');
    const [file] = event.currentTarget.files;
    if (file) {
        const fileUrl = URL.createObjectURL(file);
        previews.forEach((element) => {
            event.currentTarget.classList.add('hidden');
            element.classList.add('hidden');
            if (element instanceof HTMLImageElement && file.type.includes('image')) {
                element.src = fileUrl;
                element.classList.remove('hidden');
            }
            if (element instanceof HTMLCanvasElement && file.type.includes('image')) {
                element.dataset.src = fileUrl;
                element.classList.remove('hidden');
            }
            if (element instanceof HTMLObjectElement && 'application/pdf' === file.type) {
                element.classList.remove('hidden');
                element.data = fileUrl;
            }
            if (element instanceof HTMLVideoElement && 'video/mp4' === file.type) {
                element.classList.remove('hidden');
                element.src = fileUrl;
            }
        });
    }
}

const getFile = (event) => {
    event.preventDefault();
    const formGroupFile = event.currentTarget.closest('.form-group-file');
    const inputFile = formGroupFile.querySelector('input[type="file"].input-file');
    if (inputFile) {
        inputFile.click();
        inputFile.addEventListener('change', (event) => {
            let filename = event.currentTarget.value.split('\\').pop();
            formGroupFile.querySelector('#filename').textContent = filename;
        });
    }

    return false;
}