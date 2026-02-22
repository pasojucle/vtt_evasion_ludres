import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["frame", "container", "dialog"];
    static values = {
        eventName: String,
        url: String,
    }

    connect() {
        this.openWithContentHandler = this.openWithContent.bind(this);
        document.addEventListener("modal:openWithContent", this.openWithContentHandler);
        // this.followLinkHandler = this.followLink.bind(this);
        // document.addEventListener("modal:followLink", this.followLinkHandler);

        this.dialogTarget.addEventListener('click', (event) => {
            if (event.target === this.dialogTarget) {
                this.close();
            }
        });
    }

    disconnect() {
        document.removeEventListener("modal:openWithContent", this.openWithContentHandler);
        // document.removeEventListener("modal:followLink", this.followLinkHandler);
    }

    open() {
        console.log("open modal")
        if (!this.dialogTarget.open) {
            this.dialogTarget.showModal();

            setTimeout(() => {
                // this.containerTarget.classList.remove('-translate-y-full');
                this.containerTarget.classList.replace('-translate-y-full', 'translate-y-0');
                this.containerTarget.classList.add('lg:translate-y-20');
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
        this.containerTarget.classList.remove('lg:translate-y-20');

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

    handleFormSubmit(event) {
        console.log("handleFormSubmit");
        if (event.detail.success) {
            this.close();
            Turbo.visit(window.location.href, { action: "replace" });
        } else {
            this.frameTarget.scrollTop = 0;
        }
    }

    followLink(event) {
        const url = event.currentTarget.dataset.modalUrlValue;
        if (url) {
            this.close();
            window.location.href = url;
        }
    }
}