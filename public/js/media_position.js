
$(function() {
    document.querySelectorAll('.media-position').forEach(canvas => {
        new MediaPosition(canvas);
    });;
});



class MediaPosition {
    canvas
    context;
    reader;
    image = new Image();
    ratio = 1;

    startX = 0;
    startY = 0;
    currentY = 0;

    zones = [];
    imagePosition;
    display = {};
    gap = 10;
    fileInput;

    constructor(canvas) {
        this.display = {'width': 350, 'height': 0};
        this.canvas = canvas;
        this.context = canvas.getContext("2d");

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


        console.log('onLoad', this.display.width, this.image.width);
        console.log('onLoad', this.display.width, this.image);
        this.zones = [
            {'name': 'landscape', 'outputWidth': 1920, 'outputHeight': 1080},
            {'name': 'square', 'outputWidth': 850, 'outputHeight': 850},
        ];

        this.ratio = this.display.width / this.image.width;
        this.display['height'] = this.image.height * this.ratio;

        Object.entries(this.zones).forEach(([key, zone]) => 
            this.zones[key]['zone'] = new Zone(this.display, key, zone)
        );

        if (this.image.width > this.image.height) {
            this.canvas.width = this.image.width * this.ratio;
            this.canvas.height = this.image.height * this.ratio *2;
            Object.entries(this.zones).forEach(([key, zone]) => zone.zone.setOrigin(0, (this.display.height + this.gap) * key));
        } else {
            this.canvas.width = this.image.width * this.ratio *2;
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

    startY = 0;
    currentY = 0;
    constructor(display, key, zone) {
        this.name = zone.name;
        if(zone.outputWidth / zone.outputHeight <  display.width / display.height) {
            this.ratio =  display.height / zone.outputHeight;
        } else {
            this.ratio =  display.width / zone.outputWidth;
        }
        this.inputselector = document.querySelector('#background_' + zone.name + 'Position');
        this.imagePositions =  (this.inputselector && this.inputselector.value !== '') ? JSON.parse(this.inputselector.value) : {'positionX': null, 'positionY': null};
        this.height = zone.outputHeight * this.ratio;
        this.width = zone.outputWidth * this.ratio;

        if (this.imagePositions.positionX !== null && this.imagePositions.positionY !== null) {
            this.positionX = this.imagePositions.positionX * this.ratio;
            this.positionY = this.imagePositions.positionY * this.ratio;
        } else {
            this.defaultPositions(display);
        }
    }
    StartDraggable(mouseX, mouseY) {
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
            this.positionY = mediaPosition.currentY - mediaPosition.startY;
            if (this.positionY <= 0 || (this.positionY + this.height) >= mediaPosition.canvas.height) {
                if (this.positionY < 0) {
                    this.positionY = 0;
                }
                if ((this.positionY + this.height) > mediaPosition.canvas.height) {
                    this.positionY = mediaPosition.canvas.height - this.height;
                }
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
        this.inputselector.value = JSON.stringify({'positionX': this.positionX, 'positionY': this.positionY});
        console.log(this.inputselector.value);
    }
    defaultPositions(display) {
        this.positionX = ( display.width - this.width) / 2;
        this.positionY = ( display.height  - this.height) / 2;
        this.setPositions();
    }
}