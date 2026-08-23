import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        token: String,
    };
    static targets = ["input", "status"];

    connect() {
        console.log("entity_toggle_controller")
    }

    async toggle(event) {
        if (!this.hasUrlValue) {
            throw new Error("Missing url");
        }
        if (!this.hasTokenValue) {
            throw new Error("Missing token");
        }
        if (!this.hasUrlValue || !this.hasTokenValue) {
            throw new Error("Missing parameters");
        }

        const data = new FormData();
        data.append("csrfToken", this.tokenValue);
                    console.log("data", data)


        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                body: data,
            });

            if (!response.ok) {
                throw new Error('Something went wrong.');  
            }
            const htmlResult = await response.text();

            console.log("htmlResult", htmlResult)
            console.log("frame", this.hasStatusTarget)
            if (this.hasStatusTarget) {
                console.log("frame", this.statusTarget)
                this.statusTarget.outerHTML = htmlResult;;
            }
            const flashFrame = document.getElementById('turbo_flash_messages');
            if (flashFrame) {
                flashFrame.src = '/admin/flashes';
                flashFrame.reload();
            }
        } catch (error) {
            this.inputTarget.checked = !this.inputTarget.checked;
            this.inputTarget.disabled = false;
            console.error(error);
        }
    }
}