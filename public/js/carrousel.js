var hidden, visibilityChange;
if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support
  hidden = "hidden";
  visibilityChange = "visibilitychange";
} else if (typeof document.msHidden !== "undefined") {
  hidden = "msHidden";
  visibilityChange = "msvisibilitychange";
} else if (typeof document.webkitHidden !== "undefined") {
  hidden = "webkitHidden";
  visibilityChange = "webkitvisibilitychange";
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.slider, .full-background')) {
        let carrousel = document.querySelector('.slider, .full-background');
        carrousel.addPictures();
        carrousel.run();
    }
    document.addEventListener(visibilityChange, handleVisibilityChange, false);
  }, false);

const resizeObserver = new ResizeObserver(entries => {
    for (let entry of entries) {
        if (entry.target.pictures.length > 0) {
            entry.target.setSizes();
            entry.target.resise();
        }
    }
});

if (document.querySelector('.slider, .full-background')) {
    resizeObserver.observe(document.querySelector('.slider, .full-background'));
}

function handleVisibilityChange() {
    const carrouselElement = document.querySelector('.slider, .full-background');
}

class Carrousel extends HTMLDivElement {
    type;
    pictures = [];
    currentIndex = 0;
    lastIndex = null;
    interval;
    firstTime = 3;
    time = 6;

    constructor() {
        super();
        this.setType();
        this.setSizes();
        
    }
    setType() {
        if (this.classList.contains('slider')) {
            this.height = this.offsetHeight;
            this.width = this.offsetWidth;
        }
        this.type = (this.classList.contains('full-background')) ? 'background' : 'slider'
    }
    setSizes() {
        if (this.type === 'slider') {
            this.height = this.offsetHeight;
            this.width = this.offsetWidth;
        } else {
            this.height = window.innerHeight;
            this.width = window.innerWidth;
        }
    }
    addPictures() {
        const pictures = this.querySelectorAll('picture');
        pictures.forEach((picture, index) => {
            this.pictures[index] = new SliderPicture(index, picture, this);
        });
    }
    run() {
        this.slide();
        if (this.pictures.length > 1) {
            this.lastIndex = this.pictures.length - 1;
            setTimeout(() => {
                this.nextPicture();
                this.slide();
                this.interval = window.setInterval(() => {this.nextPicture(); this.slide();}, this.time * 1000);
            }, this.firstTime * 1000);

            
        }
    }
    stop() {
        localStorage.setItem('last_index', this.lastIndex);
        localStorage.setItem('current_index', this.currentIndex);
        clearInterval(this.interval);
    }
    restart() {
        this.clastIndex = localStorage.getItem('last_index');
        this.currentIndex = localStorage.getItem('current_index');
        this.interval = window.setInterval(() => {this.nextPicture(); this.slide();}, this.time * 1000);
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

window.customElements.define('my-carrousel', Carrousel, { extends: "div"});

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
        this.resize();
    }
    resize() {
        let ratio = (this.carrousel.width / this.picture.lastElementChild.naturalWidth > this.carrousel.height / this.picture.lastElementChild.naturalHeight) 
        ? this.carrousel.width / this.picture.lastElementChild.naturalWidth
        : this.carrousel.height / this.picture.lastElementChild.naturalHeight

        this.width = this.picture.lastElementChild.naturalWidth * ratio;
        this.heigh = this.picture.lastElementChild.naturalHeight * ratio;
        this.left = (this.carrousel.width - this.width) / 2;
        this.top =  (this.carrousel.height - this.heigh) / 2;

        this.picture.style.width = this.width + 'px';
        this.picture.style.height = this.heigh + 'px';

        if (this.carrousel.type === 'slider') {
            this.picture.style.left = (this.index === this.carrousel.currentIndex) ? 0 : this.width + 'px';
        }

        this.picture.lastElementChild.style.width = this.width + 'px';
        this.picture.lastElementChild.style.height = this.heigh + 'px';
        this.picture.lastElementChild.style.left = this.left+'px';
        this.picture.lastElementChild.style.top = this.top + 'px';
        
        return this.picture.lastElementChild;
    }
    slide() {
        if (this.carrousel.type === 'slider') {
            this.picture.style.left = '0px';
        }
        this.picture.classList.remove('loaded');
        this.picture.classList.add('playing');
    }
    back() {
        this.picture.classList.remove('playing');
        this.picture.classList.add('back');
        if (this.carrousel.type === 'slider') {
            setTimeout(() => {this.picture.style.left = this.width + 'px';}, 2 * 1000);
        }
        setTimeout(() => {
            this.picture.classList.remove('back');
            this.picture.classList.add('loaded');
        }, 3 * 1000);
    }
}
