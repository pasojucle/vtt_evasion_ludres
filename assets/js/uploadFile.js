class UploadFile extends HTMLDivElement {
    constructor() {
        super();
        this.form = this.querySelector('form');
        this.dropArea = this.querySelector('.drop-area')
        this.input = this.querySelector('input[type="file"]');
        this.progressBar = document.getElementById('progress-bar')
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
        this.input.addEventListener('change', this.handleFiles);
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
      
    handleDrop = (event) => {
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
        console.log('file number', fileNumber)
        console.log('percent', percent)
        this.uploadProgress[fileNumber] = percent;
        let total = this.uploadProgress.reduce((tot, curr) => tot + curr, 0) / this.uploadProgress.length;
        console.log('total', total)
        this.progressBar.value = total;
    }
    handleFiles = (files) => {
        files = [...files];
        this.initializeProgress(files.length);
        files.forEach(($file, i) => {this.uploadFile($file, i)});
    }
    uploadFile = (file, i) => {
        var url = this.form.action;
        var xhr = new XMLHttpRequest()
        var formData = new FormData()
        xhr.open('POST', url, true)
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
      
        xhr.upload.addEventListener("progress", (e) => {
          this.updateProgress(i, (e.loaded * 100.0 / e.total) || 100)
        });
      
        xhr.addEventListener('readystatechange', (e,i) => {
          if (xhr.readyState == 4 && xhr.status == 200) {
            this.updateProgress(i, 100) // <- Add this
          }
          else if (xhr.readyState == 4 && xhr.status != 200) {
            // Error. Inform the user
          }
        })
        let token = this.form.elements.namedItem('upload_file[_token]');
        formData.append(token.name, token.value)
        formData.append('file', file)
        xhr.send(formData)
    }
}

window.customElements.define('my-uploadfile', UploadFile, { extends: "div"});