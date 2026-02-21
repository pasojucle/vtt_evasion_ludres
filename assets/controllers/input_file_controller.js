import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["media", "input", "filename", "trigger"];

    previewFile(event) {
        const file = event.target.files[0];
        if (!file) return;
        event.currentTarget.classList.add('hidden');

        this.mediaTarget.classList.add('hidden');
        const fileUrl = URL.createObjectURL(file);
        this.mediaTargets.forEach((media) => {
            switch(true) {
                case media instanceof HTMLImageElement && file.type.includes('image'):
                    media.src = fileUrl;
                    media.classList.remove('hidden');
                    break;
                case media instanceof HTMLCanvasElement && file.type.includes('image'):
                    media.dataset.src = fileUrl;
                    media.classList.remove('hidden');
                    break;
                case media instanceof HTMLObjectElement && 'application/pdf' === file.type:
                    media.classList.remove('hidden');
                    media.data = fileUrl;
                    break;
                case media instanceof HTMLVideoElement && 'video/mp4' === file.type:
                    media.classList.remove('hidden');
                    media.src = fileUrl;
                    break;
                default:
                    media.classList.add('hidden');
                    media.src = undefined;
            }
        })
                      
        if (this.hasFilenameTarget) {
            this.filenameTarget.textContent = event.target.value.split('\\').pop();
        } 
    }

    addSrc(media,) {
        media.classList.remove('hidden');
        media.src = fileUrl;
    }

    removeSrc(media) {
        media.classList.add('hidden');
        media.src = undefined;
    }

    selectFile(event) {
        event.preventDefault();
        if (this.inputTarget) {
            this.inputTarget.click();
        }

        return false;
    }
}