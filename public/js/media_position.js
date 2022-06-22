(function( $ ){
    $.fn.mediaPosition = function(options) {
        var defaults = {
            displayWidth: 584,
            displayHeight: 225,
        };

        options = $.extend(defaults, options);

        return this.each(function() {
            var canvas, context;
            var canvasImage = new Image();
            var isDraggable = false;

            var startY = 0;
            var currentY = 0;
            var ratioImg = 1;
            var ratioCanvas = 1;
            var zone = {};
            var imagePosition;

            this.initialize = function() {
                canvas = this;
                context = canvas.getContext("2d");
                canvasImage.src = $(canvas).data('src');
                const form = $(canvas).closest("form")[0];
                const prefix = form.name;

                console.log(this);

                canvasImage.onload = function() {
                    console.log('onload');
                    ratioImg = canvas.width / canvasImage.width;
                    ratioCanvas = canvas.width / options.displayWidth;
                    canvas.height = canvasImage.height * ratioImg;
                    currentY = canvas.height;
                    zone.positionX = 0;
                    zone.width = options.displayWidth * ratioCanvas;
                    zone.height = options.displayHeight * ratioCanvas;
                    imagePosition = $('#'+prefix+'_mediaPositionY');
                    if (!imagePosition.val() || (imagePosition.val() * ratioCanvas +zone.height) > currentY) {
                        zone.positionY = (currentY-zone.height)/2;
                        imagePosition.val(zone.positionY / ratioCanvas) ;
                    } else {
                        zone.positionY = imagePosition.val() * ratioCanvas;
                    }
                    run();
                };
                const fileInput = document.getElementById(prefix+'_backgroundFile');
                fileInput.onchange = function() {
                    console.log(canvasImage);
                    canvasImage.src = URL.createObjectURL(fileInput.files[0]);
                };
                return this;
            };


            var run = function() {
                mouseEvents();
                setInterval(function() {
                    resetCanvas();
                    drawZone();
                }, 1000/30);
            };
            var resetCanvas = function() {
                context.drawImage(canvasImage, 0, 0, canvasImage.width, canvasImage.height, 0, 0, canvas.width, canvas.height);
            };
            var mouseEvents = function() {
                canvas.onmousedown = function(e) {
                    var mouseY = e.pageY - $(this).offset().top;
                    startY = mouseY - zone.positionY ;

                    if (mouseY >= zone.positionY &&
                        mouseY <= (zone.positionY + zone.height)) {
                        isDraggable = true;
                        document.body.style.cursor = "move";
                    }
                };
                canvas.onmousemove = function(e) {
                    if (isDraggable) {
                        currentY = e.pageY - $(this).offset().top;
                        zone.positionY = currentY - startY;

                        if (zone.positionY <= 0 || (zone.positionY + zone.height) >= canvas.height) {
                            if (zone.positionY < 0) {
                                zone.positionY = 0;
                            }
                            if ((zone.positionY + zone.height) > canvas.height) {
                                zone.positionY = canvas.height - zone.height;
                            }
                        }
                    }
                };
                document.onmouseup = function(e) {
                    isDraggable = false;
                    document.body.style.cursor = "auto";
                    imagePosition.val(zone.positionY / ratioCanvas);
                };
            };
            var drawZone = function() {
                context.fillStyle = 'rgba(0, 0, 0, 0.4)';
                context.fillRect(zone.positionX,0, zone.width, zone.positionY);
                context.fillRect(zone.positionX,zone.positionY + zone.height, zone.width, canvas.height - zone.positionY - zone.height);
            };

            return this.initialize();
        });
    };
})( jQuery );
$(function() {
    // $('.media-position').mediaPosition();
    document.querySelectorAll('.media-position').forEach(canvas => {
        console.log(canvas);
        new MediaPosition(canvas);
    });;
});



class MediaPosition {
    canvas
    context;
    image = new Image();
    orientation;
    ratio = 1;

    startY = 0;
    currentY = 0;

    zoneLandscape;
    zoneSquare;
    imagePosition;
    display = {};
    gap = 10;
    
    prefix;
    constructor(canvas) {
        this.display.width = 350;
        this.canvas = canvas;
        this.context = canvas.getContext("2d");

        this.image.src = canvas.dataset.src;
        const form = $(canvas).closest("form")[0];
        let prefix = form.name;
        this.image.onload = this.initialize();
        const fileInput = document.getElementById(prefix+'_backgroundFile');
        const self = this;
        fileInput.onchange = function() {
            self.canvasImage.src = URL.createObjectURL(fileInput.files[0]);
        };
    }
    initialize() {
        console.log('onload');

        this.ratio = this.display.width / this.image.width;
        this.display.height = this.image.height * this.ratio;
        if (this.image.width > this.image.height) {
            this.canvas.width = this.image.width * this.ratio;
            this.canvas.height = this.image.height * this.ratio *2;
            this.display.landscapeX = 0;
            this.display.landscapeY = 0;
            this.display.squareX = 0;
            this.display.squareY = this.display.height + this.gap;
        } else {
            this.canvas.width = this.image.width * this.ratio *2;
            this.canvas.height = this.image.height * this.ratio;
            this.display.landscapeX = 0;
            this.display.landscapeY = 0;
            this.display.squareX = this.display.width + this.gap;
            this.display.squareY = 0;
        }
        
        this.zoneLandscape = new zone(this, 'landscape', 1520, 1080);
        this.currentY = this.display.height;


        this.imagePosition = $('#'+this.prefix+'_mediaPositionY');

        this.run();
    }

    run() {
        console.log('this', this);
        this.mouseEvents();
        self = this;
        setInterval(function() {
            self.resetCanvas();
            self.drawZone();
        }, 1000/30);
    }
    resetCanvas() {
        this.context.drawImage(this.image, 0, 0, this.image.width, this.image.height, this.display.landscapeX, this.display.landscapeY, this.display.width, this.display.height);
        this.context.drawImage(this.image, 0, 0, this.image.width, this.image.height, this.display.squareX, this.display.squareY, this.display.width, this.display.height);
    }
    mouseEvents() {
        self = this;
        this.canvas.onmousedown = function(e) {
            var mouseY = e.pageY - e.target.offsetTop;
            self.startY = mouseY - self.zoneLandscape.positionY ;

            if (mouseY >= self.zoneLandscape.positionY &&
                mouseY <= (self.zoneLandscape.positionY + self.zoneLandscape.height)) {
                    self.isDraggable = true;
                document.body.style.cursor = "move";
            }
        };
        this.canvas.onmousemove = function(e) {
            if (self.isDraggable) {
                self.currentY = e.pageY - e.target.offsetTop;
                self.zone.positionY = self.currentY - self.startY;

                if (self.zoneLandscape.positionY <= 0 || (self.zone.positionY + self.zoneLandscape.height) >= self.canvas.height) {
                    if (self.zoneLandscape.positionY < 0) {
                        self.zoneLandscape.positionY = 0;
                    }
                    if ((self.zoneLandscape.positionY + self.zoneLandscape.height) > self.canvas.height) {
                        self.zoneLandscape.positionY = self.canvas.height - self.zoneLandscape.height;
                    }
                }
            }
        };
        document.onmouseup = function(e) {
            self.isDraggable = false;
            document.body.style.cursor = "auto";
            self.imagePosition.val(self.zoneLandscape.positionY / self.ratio);
        };
    };
    drawZone() {
        this.context.fillStyle = 'rgba(0, 0, 0, 0.9)';
        this.context.fillRect(0,0, this.display.width, this.zoneLandscape.positionY);
        this.context.fillRect(0,0, this.zoneLandscape.positionX, this.display.height);
        this.context.fillRect(0,this.zoneLandscape.positionY+this.zoneLandscape.height, this.display.width, this.display.height-this.zoneLandscape.height-this.zoneLandscape.positionY);
        this.context.fillRect(this.zoneLandscape.positionX+this.zoneLandscape.width,0, this.display.width-this.zoneLandscape.width-this.zoneLandscape.positionX, this.display.height);
    };
}

class zone {

    isDraggable = false;
    width;
    height
    ratio;
    positionsX;
    positionY;
    inputselector;
    imagePositions;

    startY = 0;
    currentY = 0;
    constructor(canvas, name, outputWidth, outputHeight) {
        if(outputWidth / outputHeight < canvas.display.width / canvas.display.height) {
            this.ratio = canvas.display.height / outputHeight;
        } else {
            this.ratio = canvas.display.width / outputWidth;
        }
        
        this.inputselector = document.querySelector('#background_' + name + 'Position');
        this.imagePositions =  (this.inputselector) ? JSON.parse(this.inputselector.value) : {};
        this.height = outputHeight * this.ratio;
        this.width = outputWidth * this.ratio;

        if (this.imagePositions.positionX || this.imagePositions.positionY) {

            this.positionX = this.imagePositions.positionX * this.ratio;
            this.positionY = this.imagePositions.positionY * this.ratio;
        } else {
            this.positionX = (canvas.display.width- this.width) / 2;
            this.positionY = (canvas.display.height - this.height)/2;
        }
    }
}