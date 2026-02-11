import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["frame", "btnLeft", "btnRight", "fullScreen", "formLog", "slideshow"];
    static values = {
        wsImages: String,
        wsLogs: String,
        latestView: String,
        time: { type: Number, default: 6 },
        firstTime: { type: Number, default: 3 },
        frameIndex: { type: Number, default: 0 },
        leftIndex: { type: Number, default: 0 },
        rightIndex: { type: Number, default: 0 },
        slideWith: { type: Number, default: 0 },
    };

    connect() {
        this.images = [];
        this.interval = null;
        console.log("slideShow")
        this.resizeListener = () => this.resize();
        window.addEventListener("resize", this.resizeListener);
        screen.orientation?.addEventListener("change", this.resizeListener);

        this.addImages();
    }

    disconnect() {
        window.removeEventListener("resize", this.resizeListener);
        screen.orientation?.removeEventListener("change", this.resizeListener);
        this.stop();
    }

    async addImages() {
        const url = this.wsImagesValue.replace("0", this.latestViewValue);
        try {
            const response = await fetch(url);
            const json = await response.json();
            this.images = json.images.map((img, index) => new SliderImage(index, img, this));
            this.init();
        } catch (error) {
            console.error("Erreur chargement images:", error);
        }
    }

    init() {
        if (this.images.length > 0) {
            this.find(this.frameIndexValue).append(0);
        }
        if (this.images.length > 1) {
            this.lastIndexValue = this.images.length - 1;
            this.leftIndexValue = this.lastIndexValue;
            this.rightIndexValue = 1;
            this.find(this.lastIndexValue).append(-1);
            this.find(this.rightIndexValue).append(1);
            this.btnLeftTarget.addEventListener('click', () => {this.slideToLeft()});
            this.btnRightTarget.addEventListener('click', () => {this.slideToRight()});
        }
        this.fullScreenTarget.addEventListener('click', () => {this.toggleFullScreen()});
        this.resize();
    }

    nextImage() {
        this.leftIndexValue = this.nextIndex(this.leftIndexValue);
        this.frameIndexValue = this.nextIndex(this.frameIndexValue);
        this.rightIndexValue = this.nextIndex(this.rightIndexValue);
        this.find(this.rightIndexValue).append(1);
    }

    prevImage() {
        this.leftIndexValue = this.prevIndex(this.leftIndexValue);
        this.frameIndexValue = this.prevIndex(this.frameIndexValue);
        this.rightIndexValue = this.prevIndex(this.rightIndexValue);
        this.find(this.leftIndexValue).append(-1);
    }

    nextIndex(index) {
        ++index;
        if (index === this.images.length) {
            index = 0;
        }
        return index;
    }

    prevIndex(index) {
        --index;
        if (index < 0 ) {
            index = this.images.length - 1;
        }
        return index;
    }

    slideToLeft() {
        this.find(this.leftIndexValue).imageEl.remove();
        this.find(this.frameIndexValue).slide(-1);
        this.find(this.rightIndexValue).slide(0);
        this.nextImage();
    }

    slideToRight() {
        this.find(this.rightIndexValue).imageEl.remove();
        this.find(this.frameIndexValue).slide(1);
        this.find(this.leftIndexValue).slide(0);
        this.prevImage();
    }

    toggleFullScreen = () => {
        if (!document.fullscreenElement) {
            this.slideshowTarget.requestFullscreen()
                .then(() => {
                    this.slideWithValue = window.innerWidth;
                    this.togglePosition();
                    this.slideshowTarget.querySelector('.btn-slide.full-screen i').classList.replace('fa-maximize', 'fa-minimize');
                })
                .catch((err) => {
                alert(
                    `Error attempting to enable fullscreen mode: ${err.message} (${err.name})`,
                );
                });
        } else {
            document.exitFullscreen()
                .then(() => {
                    this.slideWithValue = this.offsetWidth;
                    this.togglePosition();
                    this.slideshowTarget.querySelector('.btn-slide.full-screen i').classList.replace('fa-minimize', 'fa-maximize');
                });
        }
    }

    togglePosition = () => {
        if (Number.isInteger(this.leftIndexValue)) {
            this.find(this.leftIndexValue).slide(-1);
        }
        if (Number.isInteger(this.rightIndexValue)) {
            this.find(this.rightIndexValue).slide(1);
        }
    }

    resize() {
        let width, height;
        if (screen.orientation?.type.includes('landscape')) {
            height = window.innerHeight - this.slideshowTarget.getBoundingClientRect().top;
            width = (16/9) * height;
        } else {
            width = window.innerWidth;
            height = (16/9) * width;
        }
        
        this.slideshowTarget.style.width = `${width}px`;
        this.slideshowTarget.style.height = `${height}px`;
        this.slideWidthValue = width;
        this.togglePosition();
    }

    find(index) {
        return this.images.find(img => img.index === index);
    }

    stop() {
        clearInterval(this.interval);
    }
}


class SliderImage {
    slideshow;
    index;
    image;
    imageEl;
    heigh;
    width;
    top;
    loader;
    badge;
    viewed;
    constructor(index, image, slideshow) {
        this.slideshow = slideshow
        this.index = index;
        this.url = image.url;
        this.id = image.id;
        this.novelty = image.novelty;
        this.directory = image.directory;
        this.viewed = false;
    }
    getOrientation = () => {
        return (this.image.height < this.image.width) ? 'landscape' : 'portrait';
    }
    append = (position) => {
        this.image = new Image();
        this.image.src = this.url;
        this.imageEl = document.createElement('div');
        this.imageEl.classList.add('loader-container');
        this.slide(position);
        this.loader = document.createElement('div');
        this.loader.classList.add('loader');
        this.imageEl.append(this.loader);
        if (position = -1) {
            this.slideshow.frameTarget.prepend(this.imageEl);
        } else {
            this.slideshow.frameTarget.append(this.imageEl);
        }
        this.appendLabel();
        
        this.image.onload = () => {
            if (this.image.complete) {
                this.image.classList.add(this.getOrientation());
                this.loader.replaceWith(this.image);
                this.appendBadge();
            }
        }
    }
    appendLabel = () => {
        const $label = document.createElement('div');
        $label.classList.add('label');
        $label.textContent = `${this.directory} - ${this.index + 1} / ${this.slideshow.images.length}`;
        this.imageEl.append($label);
    }
    appendBadge = () => {
        if (this.novelty) {
            this.badge = document.createElement('div');
            this.badge.classList.add('novelty');
            this.badge.textContent = 'N';
            this.badge.style.left = (this.imageEl.offsetWidth - this.image.offsetWidth) / 2 + 20 +'px';
            let top = (this.imageEl.offsetHeight - this.image.offsetHeight) / 2 + 20;
            if (top < 20) {
                top = 20;
            }
            this.badge.style.top = top +'px';
            this.imageEl.append(this.badge);
        }
    }
    slide(position) {
        this.imageEl.style.left = position * this.slideshow.slideWidthValue + 'px';
        if (position === 0) {
            this.writeLog();
            this.viewed = true;
        } else {
            if (this.badge !== undefined) {
                this.badge.remove();
            }
            if (this.viewed) {
                this.novelty = false;
            }
        }
    }
    writeLog = async() => {
        const data = new FormData(this.slideshow.formLogTarget);
        data.append('log[entityId]', this.id);
        const url = this.slideshow.wsLogsValue;        

        await fetch(url,{
            method: 'POST',
            body : data, 
        });
    }
}