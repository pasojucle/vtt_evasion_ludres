document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.media-position').forEach(canvas => {
        new MediaPosition(canvas);
    });
  }, false);

class MediaPosition {
    canvas
    context;
    reader;
    image = new Image();
    ratio = 1;

    startX = 0;
    startY = 0;
    currentY = 0;
    currentX = 0;

    zones = [];
    imagePosition;
    display = {};
    gap = 10;
    fileInput;

    constructor(canvas) {
        this.display = {};
        this.canvas = canvas;
        this.context = canvas.getContext("2d");
        this.ratio;

        this.reader = new FileReader()

        this.image.src = canvas.dataset.src;
        const form = $(canvas).closest("form")[0];
        let prefix = form.name;
        this.image.onload = this.initialize();
        const self = this;
        
        this.fileInput = document.getElementById(prefix+'_backgroundFile');
        
        this.fileInput.onchange = (e => this.changeImage());
    }
    initialize(e) {
        this.display.width = (this.image.width > this.image.height) ? 400 : 225;

        console.log('onLoad', this.display.width, this.image.width);
        console.log('onLoad', this.display.width, this.image);
        this.zones = [
            {'name': 'landscape', 'outputWidth': 1920, 'outputHeight': 1080},
            {'name': 'portrait', 'outputWidth': 1080, 'outputHeight': 1920},
            {'name': 'square', 'outputWidth': 850, 'outputHeight': 850},
        ];

        this.ratio = this.display.width / this.image.width;
        this.display.height = this.image.height * this.ratio;

        Object.entries(this.zones).forEach(([key, zone]) => 
            this.zones[key]['zone'] = new Zone(this, zone)
        );

        if (this.image.width > this.image.height) {
            this.canvas.width = this.image.width * this.ratio;
            this.canvas.height = this.image.height * this.ratio * this.zones.length;
            Object.entries(this.zones).forEach(([key, zone]) => zone.zone.setOrigin(0, (this.display.height + this.gap) * key));
        } else {
            this.canvas.width = this.image.width * this.ratio * this.zones.length;;
            this.canvas.height = this.image.height * this.ratio;
            Object.entries(this.zones).forEach(([key, zone]) => zone.zone.setOrigin((this.display.width + this.gap) * key, 0));
        }

        this.imagePosition = $('#'+this.prefix+'_mediaPositionY');

        this.run();
    }
    run() {
        console.log('this', this);
        this.mouseEvents();
        self = this;
        this.interval = setInterval(function() {
            self.drawImage();
            Object.entries(self.zones).forEach(([key, zone]) => self.drawZone(zone.zone));
        }, 1000/30);
    }
    drawImage() {
        Object.entries(this.zones).forEach(([key, zone]) => 
            this.context.drawImage(this.image, 0, 0, this.image.width, this.image.height, zone.zone.origin.x, zone.zone.origin.y, this.display.width, this.display.height)
        );
    }
    mouseEvents() {
        self = this;
        this.canvas.onmousedown = function(e) {
            var mouseY = e.pageY - e.target.offsetTop;
            var mouseX = e.pageX - e.target.offsetLeft;

            Object.entries(self.zones).forEach(([key, zone]) => zone.zone.StartDraggable(mouseX, mouseY));

        };
        this.canvas.onmousemove = function(e) {
            self.currentY = e.pageY - e.target.offsetTop;
            self.currentX = e.pageX - e.target.offsetLeft;
            Object.entries(self.zones).forEach(([key, zone]) => zone.zone.move(self));
        };
        document.onmouseup = function(e) {
            Object.entries(self.zones).forEach(([key, zone]) => zone.zone.endDraggable());

        };
    };
    drawZone(zone) {
        this.context.fillStyle = 'rgba(0, 0, 0, 0.7)';
        //top
         this.context.fillRect(zone.origin.x,zone.origin.y, this.display.width, zone.positionY);
        //bottom
        this.context.fillRect(zone.origin.x,zone.positionY+zone.height, this.display.width, this.display.height-zone.height-zone.positionY);
        //left
        this.context.fillRect(zone.origin.x,zone.origin.y, zone.positionX, this.display.height);
        //rigth
        this.context.fillRect(zone.positionX+zone.width,zone.origin.y, this.display.width-zone.width-zone.positionX, this.display.height);
    }
    changeImage() {
        this.image = new Image();
        this.image.src = URL.createObjectURL(this.fileInput.files[0]);
        var reader = new FileReader()
        reader.onload = async (e) => {
            let image = new Image()
            image.src = e.target.result
            await image.decode()
                    self.initialize();
                    Object.entries(self.zones).forEach(([key, zone]) => 
                    zone.zone.defaultPositions(self.display)
                );
            }
        reader.readAsDataURL(this.fileInput.files[0]);
    }
}

class Zone {
    isDraggable = false;
    width;
    height
    ratio;
    positionX;
    positionY;
    inputselector;
    imagePositions;
    origin = {};
    name;
    mediaPosition;

    startY = 0;
    startX = 0;

    constructor(mediaPosition, zone) {
        this.name = zone.name;
        this.mediaPosition = mediaPosition;
        if(zone.outputWidth / zone.outputHeight <  mediaPosition.display.width / mediaPosition.display.height) {
            this.ratio =  mediaPosition.display.height / zone.outputHeight;
        } else {
            this.ratio =  mediaPosition.display.width / zone.outputWidth;
        }
        this.inputselector = document.querySelector('#background_' + zone.name + 'Position');
        this.imagePositions =  (this.inputselector && this.inputselector.value !== '') ? JSON.parse(this.inputselector.value) : {'positionX': null, 'positionY': null};
        this.height = zone.outputHeight * this.ratio;
        this.width = zone.outputWidth * this.ratio;

        if (this.imagePositions.positionX !== null && this.imagePositions.positionY !== null) {
            this.positionX = this.imagePositions.positionX * this.mediaPosition.ratio;
            this.positionY = this.imagePositions.positionY * this.mediaPosition.ratio;
        } else {
            this.defaultPositions();
        }
    }
    StartDraggable(mouseX, mouseY) {
        this.startX = mouseX - this.positionX + this.origin.x;
        this.startY = mouseY - this.positionY + this.origin.y;
        if (mouseY >= this.origin.y + this.positionY
            && mouseY <= (this.origin.y + this.positionY + this.height)
            && mouseX >= this.origin.x + this.positionX
            && mouseX <= (this.origin.x + this.positionX + this.width)
        ) {
            this.isDraggable = true;
            document.body.style.cursor = "move";
        }
    }
    setOrigin(x, y) {
        this.origin = {'x': x, 'y': y};
    }
    move(mediaPosition) {
        if (this.isDraggable) {
            this.positionY = mediaPosition.currentY - this.startY;
            this.positionX = mediaPosition.currentX - this.startX;

            if (this.positionY < 0) {
                this.positionY = 0;
            }
            if ((this.positionY + this.height) >= this.mediaPosition.display.height + this.origin.y) {
                this.positionY = this.mediaPosition.display.height + this.origin.y - this.height;
            }
            if (this.positionX < 0) {
                this.positionX = 0;
            }
            console.log(this.positionX + this.width, '/', this.mediaPosition.display.width + this.origin.x - this.width);
            console.log(this.mediaPosition.display.width,this.origin.x,this.width);
            if ((this.positionX + this.width) >= this.mediaPosition.display.width + this.origin.x) {
                this.positionX = this.mediaPosition.display.width + this.origin.x - this.width;
            }
        }
    }
    endDraggable() {
        if (this.isDraggable) {
            this.isDraggable = false;
            document.body.style.cursor = "auto";
            this.setPositions();
        }
    }
    setPositions() {
        this.inputselector.value = JSON.stringify({'positionX': this.positionX / this.mediaPosition.ratio, 'positionY': this.positionY / this.mediaPosition.ratio});
        console.log(this.inputselector.value);
    }
    defaultPositions() {
        this.positionX = ( this.mediaPosition.display.width - this.width) / 2;
        this.positionY = ( this.mediaPosition.display.height  - this.height) / 2;
        this.setPositions();
    }
}