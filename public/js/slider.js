document.addEventListener('DOMContentLoaded', () => {
    let slider = document.querySelector('.slider');
    slider.addPictures();
    slider.run();
  }, false);

const resizeObserver = new ResizeObserver(entries => {
    for (let entry of entries) {
        if (entry.target.pictures.length > 0) {
            entry.target.setSizes();
            entry.target.resise();
        }
    }
  });

resizeObserver.observe(document.querySelector('.slider'));

class Slider extends HTMLDivElement {
    pictures = [];
    currentIndex = 0;
    lastIndex = null;
    interval;
    time = 5;

    constructor() {
        super();
        this.setSizes();
    }
    setSizes() {
        this.height = this.offsetHeight;
        this.width = this.offsetWidth;
        console.log('slider', this.width, this.height);
    }
    addPictures() {
        const pictures = this.querySelectorAll('picture');
        console.log('pictures', pictures);
        pictures.forEach((picture, index) => {
            console.log('index', index);
            this.pictures[index] = new SliderPicture(index, picture, this);
        });
    }
    run() {
        console.log('run');
        this.slide();
        if (this.pictures.length > 1) {
            this.lastIndex = this.pictures.length - 1;
            this.interval = window.setInterval(() => {this.nextPicture(); this.slide();}, this.time * 1000);
        }
    }
    nextPicture() {
        this.currentIndex = this.nextIndex(this.currentIndex);
        this.lastIndex = this.nextIndex(this.lastIndex);
    }
    nextIndex(index) {
        ++index;
        if (index === this.pictures.length) {
            index = 0;
        }
        return index;
    }
    slide() {
        this.pictures[this.currentIndex].slide();
        if (null !== this.lastIndex) {
            this.pictures[this.lastIndex].back();
        }      
    }
    resise() {
        clearInterval(this.interval);
        this.pictures.forEach(picture => {picture.resize()});
        this.run();
    }
}

window.customElements.define('my-slider', Slider, { extends: "div"});

class SliderPicture {
    slider;
    index;
    picture;
    heigh;
    width;
    top;
    constructor(index, picture, slider) {
        this.slider = slider;
        this.index = this.index;
        this.picture = picture;
        this.resize();
    }
    resize() {
        console.log('ration width', this.slider.width / this.picture.lastElementChild.naturalWidth);
        console.log('ration height', this.slider.height / this.picture.lastElementChild.naturalHeight);
        let ratio = (this.slider.width / this.picture.lastElementChild.naturalWidth > this.slider.height / this.picture.lastElementChild.naturalHeight) 
        ? this.slider.width / this.picture.lastElementChild.naturalWidth
        : this.slider.height / this.picture.lastElementChild.naturalHeight

        this.width = this.picture.lastElementChild.naturalWidth * ratio;
        this.heigh = this.picture.lastElementChild.naturalHeight * ratio;
        this.left = (this.slider.width - this.width) / 2;
        this.top =  (this.slider.height - this.heigh) / 2;

        this.picture.style.width = this.width + 'px';
        this.picture.style.height = this.heigh + 'px';
        this.picture.style.left = (this.index === this.slider.currentIndex) ? 0 : this.width + 'px';

        this.picture.lastElementChild.style.width = this.width + 'px';
        this.picture.lastElementChild.style.height = this.heigh + 'px';
        this.picture.lastElementChild.style.left = this.left+'px';
        this.picture.lastElementChild.style.top = this.top + 'px';
        console.log('picture', this.width, this.heigh);

        return this.picture.lastElementChild;
    }
    slide() {
        console.log('slide', this);
        this.picture.style.left = '0px';
        this.picture.classList.remove('loaded');
        this.picture.classList.add('playing');
    }
    back() {
        console.log('back', this);
        this.picture.classList.remove('playing');
        this.picture.classList.add('back');
        setTimeout(() => {this.picture.style.left = this.width + 'px';}, 2 * 1000);
        setTimeout(() => {
            this.picture.classList.remove('back');
            this.picture.classList.add('loaded');
        }, 3 * 1000);
    }
}
