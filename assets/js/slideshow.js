import '../styles/slideshow.scss';

class Slideshow extends HTMLDivElement {
    images = [];
    leftIndex = null
    frameIndex = 0;
    rightIndex = null;
    lastIndex = null;
    interval;
    firstTime = 3;
    time = 6;
    slideWith;

    constructor() {
        super();
        this.frameEl = this.querySelector('.frame');
        this.addImages();
        this.resize();
        this.slideWith = this.offsetWidth;
        screen.orientation.addEventListener("change", (event) => {
            this.resize();
        });
    }
    init = () => {
        this.find(this.frameIndex).append(0);
        if (this.images.length > 1) {
            this.lastIndex = this.images.length - 1;
            this.leftIndex = this.lastIndex;
            this.rightIndex = 1;
            this.find(this.lastIndex).append(-1);
            this.find(this.rightIndex).append(1);
        }
        this.querySelector('.btn-slide.slide-to-left').addEventListener('click', () => {this.slideToLeft()});
        this.querySelector('.btn-slide.slide-to-right').addEventListener('click', () => {this.slideToRight()});
        this.querySelector('.btn-slide.full-screen').addEventListener('click', () => {this.toggleFullScreen()});
    }
    resize = () => {
        if (screen.orientation.type.includes('landscape')) {
            const height = window.innerHeight - this.getBoundingClientRect().top;
            this.style.height = height + 'px';
            this.style.width = 16/9*height + 'px'; 
        } else {
            const width = window.innerWidth;
            this.style.width = width + 'px';
            this.style.height = 16/9*width + 'px';
        }
        this.slideWith = this.offsetWidth;
        this.toogglePosition();
    }
    addImages = async() => {
        await fetch(Routing.generate('slideshow_images'),)
        .then((response) => response.json())
        .then((json)=> {
            json.images.forEach((url, index) => {
                this.addImage(index, url);
            })
            this.init();
        });
    }
    addImage = (index, url) => {
        this.images.push(new SliderImage(index, url, this)) 
    }
    find = (index) => {
        const image = this.images.find((image) => image.index === index);
        return image;
    }
    run() {
        if (this.images.length > 2) {
            setTimeout(() => {
                this.slideToLeft();
                this.interval = window.setInterval(() => {this.slideToLeft();}, this.time * 1000, this);
            }, this.firstTime * 1000, this);
        }
    }
    stop() {
        clearInterval(this.interval);
    }
    nextImage() {
        this.leftIndex = this.nextIndex(this.leftIndex);
        this.frameIndex = this.nextIndex(this.frameIndex);
        this.rightIndex = this.nextIndex(this.rightIndex);
        this.find(this.rightIndex).append(1);
    }
    prevImage() {
        this.leftIndex = this.prevIndex(this.leftIndex);
        this.frameIndex = this.prevIndex(this.frameIndex);
        this.rightIndex = this.prevIndex(this.rightIndex);
        this.find(this.leftIndex).append(-1);
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
        this.find(this.leftIndex).imageEl.remove();
        this.find(this.frameIndex).slide(-1);
        this.find(this.rightIndex).slide(0);
        this.nextImage();
    }
    slideToRight() {
        this.find(this.rightIndex).imageEl.remove();
        this.find(this.frameIndex).slide(1);
        this.find(this.leftIndex).slide(0);
        this.prevImage();
    }
    toggleFullScreen = () => {
        if (!document.fullscreenElement) {
            this.requestFullscreen()
                .then(() => {
                    this.slideWith = window.innerWidth;
                    this.toogglePosition();
                    this.querySelector('.btn-slide.full-screen i').classList.replace('fa-maximize', 'fa-minimize');
                })
                .catch((err) => {
                alert(
                    `Error attempting to enable fullscreen mode: ${err.message} (${err.name})`,
                );
                });
        } else {
            document.exitFullscreen()
                .then(() => {
                    this.slideWith = this.offsetWidth;
                    this.toogglePosition();
                    this.querySelector('.btn-slide.full-screen i').classList.replace('fa-minimize', 'fa-maximize');
                });
        }
    }
    toogglePosition = () => {
        if (Number.isInteger(this.leftIndex)) {
            this.find(this.leftIndex).slide(-1);
        }
        if (Number.isInteger(this.rightIndex)) {
            this.find(this.rightIndex).slide(1);
        }
    }
}

window.customElements.define('my-slideshow', Slideshow, { extends: "div"});

class SliderImage {
    slideshow;
    index;
    image;
    imageEl;
    heigh;
    width;
    top;
    loader;
    constructor(index, url, slideshow) {
        this.slideshow = slideshow
        this.index = index;
        this.url = url;
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
            this.slideshow.frameEl.prepend(this.imageEl);
        } else {
            this.slideshow.frameEl.append(this.imageEl);
        }
        
        this.image.onload = () => {
            if (this.image.complete) {
                this.image.classList.add(this.getOrientation());
                this.loader.replaceWith(this.image);
            }
        }
    }
    slide(position) {
        console.log(this.imageEl, this.slideshow.slideWith)
        this.imageEl.style.left = position * this.slideshow.slideWith + 'px';
    }
}
