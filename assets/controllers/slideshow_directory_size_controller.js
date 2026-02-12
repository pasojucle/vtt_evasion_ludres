import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["size", "progress"];

    static values = {
        url: String,
    }

    connect() {
        this.abortController = new AbortController();
        this.fetchSlideshowDirectorySize();
    }

    disconnect() {
        this.abortController.abort();
    }

    async fetchSlideshowDirectorySize() {
        try {
            const response = await fetch(this.urlValue, { signal: this.abortController.signal });
            const json = await response.json();
            
            if (this.hasSizeTarget) {
                this.sizeTarget.textContent = json.response.text;
                this.progressTarget.value = json.response.value;
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error("Erreur :", error);
            }
        }
    }
}