import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["size", "progress"];

    static values = {
        url: String,
    }

    connect() {
        console.log("slideshow-directory-size-controller")
        this.fetchSlideshowDirectorySize();
    }

    disconnect() {
;
    }

    async fetchSlideshowDirectorySize() {
        await fetch(this.urlValue)
            .then((response) => response.json())
            .then((json)=> {

                this.sizeTarget.textContent = json.response.text;
                this.progressTarget.value = json.response.value;
                this.progressTarget.classList.add(json.response.color)
            });
    }
}