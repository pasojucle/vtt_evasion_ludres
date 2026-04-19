import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["container"];

    addItem() {
        if (this.hasContainerTarget) {
            const html = this.containerTarget
                .dataset
                .prototype
                .replace(
                /__name__/g,
                this.containerTarget.dataset.index
                );

            const item = document.createRange().createContextualFragment(html)
            this.containerTarget.appendChild(item);

            // if (e.currentTarget.classList.contains('add-item-file')) {
            //     const inputFile = collectionHolder.lastChild.querySelector('input[type="file"]');
            //     inputFile.click();
            //     inputFile.addEventListener('change', (event) => {
            //     const image = document.createElement('IMG');
                // previewFile(event)
                // });    
            // }

            // if (e.currentTarget.classList.contains('add-bike-ride-track')) {
            //     collectionHolder.lastChild.querySelectorAll('.input-file-button').forEach((element) => {
            //         // element.addEventListener('click', getFile);
            //     })
            // }
        }

        
    }
}