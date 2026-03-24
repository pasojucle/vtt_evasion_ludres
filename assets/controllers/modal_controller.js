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
        this.openFromUrlHandler = this.openFromUrl.bind(this);
        document.addEventListener("modal:openFromUrl", this.openFromUrlHandler);

        this.dialogTarget.addEventListener('click', (event) => {
            if (event.target === this.dialogTarget) {
                this.close();
            }
        });
    }

    disconnect() {
        document.removeEventListener("modal:openWithContent", this.openWithContentHandler);
        document.removeEventListener("modal:openFromUrl", this.openFromUrlHandler);
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

    async openFromUrl(event) {
        const { url } = event.detail;
        console.log("openFromUrl modal")

        const response = await fetch(url);

        const html = await response.text();
        const parser = new DOMParser();
        const content = parser.parseFromString(html, 'text/html');

        if (content) {
            this.frameTarget.removeAttribute('src'); 
            this.frameTarget.innerHTML = html;
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
        const url = event.currentTarget.dataset.modalUrl;
        const target = event.currentTarget.dataset.modalLinkTarget || '_self';
        const frameId = event.currentTarget.dataset.modalFrameId;
        console.log("frameId", frameId)
        if (url) {
            this.close();
            if (target === '_blank') {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        }
        if (frameId) {
            const frame = document.getElementById(frameId);
            console.log("frame", frame)
            if (frame) {
                setTimeout(() => {
                    try {
                        frame.reload();
                        console.log("Appel de reload() effectué");
                    } catch (e) {
                        console.error("Erreur lors du reload :", e);
                    }
                }, 500);
            }
        }
    }
}