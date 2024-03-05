class UploadFile extends HTMLDivElement {
    constructor() {
        super();
        this.form = this.querySelector('form');
        this.dropArea = this.querySelector('.drop-area')
        this.input = this.querySelector('input[type="file"]');
        this.maxSize = (this.input) ? this.input.dataset.maxSize : null;
        this.progressBar = document.getElementById('progress-bar');
        this.errorMessage = document.getElementById('slideshow-error');
        this.uploadBtn = document.querySelector('.slideshow-toolbar .tools button');
        this.init();
    }
    init = () => {
        ;['dragenter', 'dragover'].forEach((eventName) => {
            this.addEventListener(eventName, this.highlight, false);
        })
        ;['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName) => {
            this.addEventListener(eventName, this.preventDefaults, false)   ;
            document.body.addEventListener(eventName, this.preventDefaults, false);
        })

        ;['dragleave', 'drop'].forEach((eventName) => {
            this.addEventListener(eventName, this.unhighlight, false);
        })
        this.addEventListener('drop', this.handleDrop, false);
        this.input.addEventListener('change', this.submitForm);
        if (this.uploadBtn) {
            this.uploadBtn.addEventListener('click', this.openFileDialog);
        }
    }
    preventDefaults = (event) => {
        event.preventDefault();
        event.stopPropagation();
    }
    highlight = () => {
        this.dropArea.classList.add('highlight');
    }
    unhighlight = () => {
        this.dropArea.classList.remove('highlight');
    }
    openFileDialog = () => {
        this.input.click();
    }
    handleDrop = (event) => {
        this.errorMessage.classList.add('d-none');
        this.errorMessage.innerText = '';
        var dataTransfer = event.dataTransfer
        var files = dataTransfer.files;
        this.handleFiles(files);
    }
    initializeProgress = (numFiles) => {
        this.progressBar.value = 0;
        this.uploadProgress = [];
      
        for(let i = numFiles; i > 0; i--) {
          this.uploadProgress.push(0);
        }
    }
    updateProgress = (fileNumber, percent) => {
        this.uploadProgress[fileNumber] = percent;
        let total = this.uploadProgress.reduce((tot, curr) => tot + curr, 0) / this.uploadProgress.length;
        this.progressBar.value = total;
    }
    handleFiles = (files) => {
        files = [...files];
        if (files.length < 1) {
            this.displayError('Aucun fichier à télécharger');
        }
        this.initializeProgress(files.length);
        files.forEach(($file, i) => {this.uploadFile($file, i)});
    }
    submitForm = (event) => {
        const files = event.target.files;
        this.initializeProgress(files.length);
        Array.from(files).forEach(($file, i) => {this.uploadFile($file, i)});
    }
    isValid = (file) => {
        if (this.maxSize < file.size) {
            this.displayError('Le fichier doit être inférieur à ' + this.input.dataset.maxSizeValue);
            return false;
        }
        if (this.dropArea.classList.contains('drop-area-disabled')) {
            this.displayError('Le fichier ne peut pas être téléchargé à la racine.');
            return false;
        }
        if (!file.type.includes('image')) {
            this.displayError('Le fichier doit être de type image.');
            return false;
        }
        return true;
    }
    uploadFile = (file, i) => {
        if (!this.isValid(file)) {
            return;
        }
        var url = this.form.action;
        var xhr = new XMLHttpRequest()

        var formData = new FormData()
        xhr.open('POST', url, true)
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
        this.progressBar.classList.remove('d-none');
        xhr.upload.addEventListener("progress", (e) => {
            this.updateProgress(i, (e.loaded * 100.0 / e.total) || 100)
        });
        xhr.addEventListener('readystatechange', (e,i) => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                this.updateProgress(i, 100)
                location.reload();
            }
            else if (xhr.readyState === 4 && xhr.status !== 200) {
                // Error. Inform the user
                this.displayError(xhr.statusText);
            }
            
        })
        let token = this.form.elements.namedItem('upload_file[_token]');
        formData.append(token.name, token.value)
        formData.append('file', file)
        xhr.send(formData)
    }
    displayError = (message) => {
        this.errorMessage.classList.remove('d-none');
        this.errorMessage.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + message;
    }
}

window.customElements.define('my-uploadfile', UploadFile, { extends: "div"});