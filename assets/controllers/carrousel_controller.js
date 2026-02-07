import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["frame", "btnLeft", "btnRight", "fullScreen"];
    static values = {
        type: String,
        currentIndex: {type:Number, default: 0},
        lastIndex: {type:Number, default: -1},
        firstTime:{type:Number, default: 3},
        time: {type:Number, default: 6},
        width: Number,
        height: Number,
        loaded: {type:Boolean, default: false},
    };

    connect() {
        this.pictures = [];
        this.interval = null;
        this.timeout = null;
        this.setType();
        this.setSizes();
        this.addPictures();
        this.visibilityHandler = () => {
            if (document.hidden) {
                this.stop();
            } else {
                this.run();
            }
        };
        document.addEventListener("visibilitychange", this.visibilityHandler);
        this.resizeObserver = new ResizeObserver(() => {
            if (this.pictures.length > 0) {
                this.setSizes();
                this.resize();
            }
        });
        this.resizeObserver.observe(this.element);
    }
    disconnect() {
        document.removeEventListener("visibilitychange", this.visibilityHandler);
        this.resizeObserver.disconnect();
        this.stop();
        this.currentIndexValue = 0;
        this.lastIndexValue = -1;
        this.slide();
    }
    setType() {
        this.typeValue = (this.element.classList.contains('full-background')) ? 'background' : 'slider'
    }
    setSizes() {
        if (this.typeValue === 'slider') {
            this.heightValue = this.element.offsetHeight;
            this.widthValue = this.element.offsetWidth;
        } else {
            this.heightValue = window.innerHeight;
            this.widthValue = window.innerWidth;
        }
    }
    addPictures() {
        const pictures = this.element.querySelectorAll('picture');
        pictures.forEach((picture, index) => {
            new SliderPicture(index, picture, this);
        });
    }
    addPicture(picture) {
        this.pictures.push(picture);
        if (this.pictures.length === 2) {
            this.loadedValue = true;
            this.run();
        }
    }
    run() {
        this.stop();
        if (!this.loadedValue) {
            return;
        }
        console.log("run")
        this.runningValue = true;
        this.interval = window.setInterval(() => {
            this.nextPicture(); 
            this.slide();
        }, this.timeValue * 1000);
    }
    stop() {
        clearInterval(this.interval);
        clearTimeout(this.timeout);
        this.runningValue = false;
    }
    nextPicture() {
        this.currentIndexValue = this.lastIndex(this.currentIndexValue);
        this.lastIndexValue = this.lastIndex(this.lastIndexValue);
    }
    lastIndex(index) {
        ++index;
        if (index === this.pictures.length) {
            index = 0;
        }
        return index;
    }
    slide() {
        if (this.pictures[this.currentIndexValue]) {
            this.pictures[this.currentIndexValue].slide();
        }
        if (this.lastIndexValue >= 0 && this.pictures[this.lastIndexValue]) {
            this.pictures[this.lastIndexValue].back();
        }      
    }
    resize() {
        clearInterval(this.interval);
        this.pictures.forEach(picture => {picture.resize()});
        this.run();
    }
}

class SliderPicture {
    carrousel;
    index;
    picture;
    heigh;
    width;
    top;
    constructor(index, picture, carrousel) {
        this.carrousel = carrousel;
        this.index = index;
        this.picture = picture;
        this.img = picture.lastElementChild;
        this.loadImage()
    }
    loadImage() {
        if (this.img.complete) {
            this.handleSuccess();
        } else {
            this.img.onload = this.handleSuccess;
            this.img.onerror = this.handleError;
        }
    }
    handleSuccess = () => {
        if (this.img.naturalWidth > 0) {
            this.carrousel.addPicture(this);
            this.resize();
        }
    }
    handleError = () => {
        this.picture.style.display = 'none';
    }

    resize() {
        let ratio = (this.carrousel.widthValue / this.img.naturalWidth > this.carrousel.heightValue / this.img.naturalHeight) 
        ? this.carrousel.widthValue / this.img.naturalWidth
        : this.carrousel.heightValue / this.img.naturalHeight

        this.width = this.img.naturalWidth * ratio;
        this.heigh = this.img.naturalHeight * ratio;
        this.left = (this.carrousel.widthValue - this.width) / 2;
        this.top = (this.carrousel.heightValue - this.heigh) / 2;

        this.picture.style.width = this.width + 'px';
        this.picture.style.height= this.heigh + 'px';

        if (this.carrousel.typeValue === 'slider') {
            // if (this.carrousel.runningValue) {
            //     this.picture.classList.add('rewind');
            // }
            this.picture.style.left = (this.index === this.carrousel.currentIndexValue) ? 0 : this.width + 'px';
        }

        this.img.style.width = this.width + 'px';
        this.img.style.height = this.heigh + 'px';
        this.img.style.left = this.left + 'px';
        this.img.style.top = this.top + 'px';
        
        return this.img;
    }
    slide() {
        if (this.carrousel.typeValue === 'slider') {
            this.picture.style.left = '0px';
            this.picture.classList.remove('rewind');
        }
        this.picture.classList.remove('loaded');
        this.picture.classList.add('playing');
    }
    back() {
        this.picture.classList.remove('playing');
        this.picture.classList.add('back');
        setTimeout(() => {
            if (this.carrousel.typeValue === 'slider') {
                this.picture.style.left = this.width + 'px';
                this.picture.classList.add('rewind');
            }
            this.picture.classList.remove('back');
            this.picture.classList.add('loaded');
        }, 2* 1000);
    }
}
