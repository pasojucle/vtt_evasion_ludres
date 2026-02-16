import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["frame", "container", "dialog"];
    static values = {
        eventName: String
    }

    connect() {
        this.openWithContentHandler = this.openWithContent.bind(this);
        document.addEventListener("modal:openWithContent", this.openWithContentHandler);

        this.dialogTarget.addEventListener('click', (event) => {
            if (event.target === this.dialogTarget) {
                this.close();
            }
        });
    }

    disconnect() {
        document.removeEventListener("modal:openWithContent", this.openWithContentHandler);
    }

    open() {
        console.log("open modal")

        if (!this.dialogTarget.open) {
            this.dialogTarget.showModal();

            setTimeout(() => {
                this.containerTarget.classList.remove('-translate-y-full');
                this.containerTarget.classList.add('translate-y-0');
            }, 10);
        }
    }

    openWithContent(event) {
        const { content } = event.detail;
        console.log("openWithContent modal")
        if (content) {
            this.frameTarget.removeAttribute('src'); 
            this.frameTarget.innerHTML = content;
        }

        this.open();
    }

    close() {
        this.containerTarget.classList.replace('translate-y-0', '-translate-y-full');

        setTimeout(() => {
            this.dialogTarget.close();
            this.frameTarget.src = "";
            this.frameTarget.innerHTML = "";
        }, 500);
    }

    handleAction(event) {
        event.preventDefault();
        const eventName = event.currentTarget.dataset.modalEventNameValue;
        const payload = event.currentTarget.dataset.modalPayloadValue;
        console.log("eventName", eventName, payload);
        if (eventName) {
            const customEvent = new CustomEvent(eventName, { 
                bubbles: true, 
                detail: { targetId: payload }
            });
            window.dispatchEvent(customEvent);
        }

        this.close();
    }
}