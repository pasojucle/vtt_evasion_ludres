document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.line-chart').forEach(canvas => {
        new LineChart(canvas);
    });
  }, false);

class LineChart {
    canvas;
    context;
    route;
    data;
    width;
    height;
    lines = [];
    padding;
    footer;
    gap;

    constructor(canvas) {
        this.display = {};
        this.id = canvas.id;
        this.canvas = canvas;
        this.context = canvas.getContext("2d");
        this.route = canvas.dataset.route;
        this.routeParams = {'isSchool': canvas.dataset.isSchool}; 
        this.ratioY = 0;
        this.delay = 1000 / 30;
        this.loop = 0;
        this.startLoop = 10;
        this.base = 100;
        this.interval = null;
        this.initialize();
    }
    initialize = async() => {
        await fetch(Routing.generate(this.route, this.routeParams),)
        .then((response) => response.json())
        .then((json)=> {
            console.log(json)
            json.membersPrecences.forEach((presences) => {
                this.addLine(presences);
            });
            this.setFormat(json.format);
            console.log('canvas', this);
            this.run();
        });
    }
    addLine = (presences) => {
        this.lines.push(new Line(this, presences));
    }
    setFormat = (format) => {
        if (format === 'card') {
            this.width = this.canvas.parentElement.offsetWidth;
            this.height = 200;
        }
        this.canvas.width = this.width;
        this.canvas.height = this.height;
        this.padding = 10;
        this.footer = 50;
        this.gap = (this.width - this.padding * 2 ) / (this.lines[0].data.length - 1);
        this.textPosition = this.height - this.footer + this.padding
    }
    next = () => {
        this.loop++;
        if (this.startLoop < this.loop) {
            this.ratioY = Math.pow(this.loop - this.startLoop, 2); 
        }
    }
    frameDelay = () => {
        this.delay = 1000 / 30;
    }
    run = () => {
        this.interval = setInterval(() => {
            this.context.clearRect(0, 0, this.width, this.height);
            this.lines.forEach((line) => {
                line.draw();
                this.next();
                if (this.base < this.ratioY) {
                    clearInterval(this.interval);
                }
            })
        }, this.delay, this);
    }
}

class Line {
    offsetX = 0;
    constructor(lineChart, presences) {
        this.lineChart = lineChart;
        this.data = presences.data;
        this.color = presences.color;
        this.markColor = 'rgba(0,0,0,0.7)'
    }
    draw = () => {
        this.drawLine();
        this.drawLandmarks();
    }
    drawLine = () => {
        this.offsetX = this.lineChart.padding;
        this.lineChart.context.lineWidth = 2.5;
        this.lineChart.context.beginPath();
        this.lineChart.context.strokeStyle = this.color;
        this.data.forEach((presence, index) => {
            
            const value = parseInt(presence[1]) * this.lineChart.ratioY / this.lineChart.base;
            this.drawItem(value, index);
            this.writeDate(presence);
            this.offsetX += this.lineChart.gap;
        })
        this.lineChart.context.stroke();
    }
    drawItem = (value, index) => {
        const offsetY = this.lineChart.height - value * 2 - 50;
        this.writeValue(value, offsetY)
        if (0 < index) {
            this.lineChart.context.lineTo(this.offsetX, offsetY );
            return;
        }
        
        this.lineChart.context.moveTo(this.offsetX, offsetY)
    }
    writeDate = (precence) => {
        this.lineChart.context.save();
        this.lineChart.context.translate(this.offsetX, this.lineChart.textPosition);
        this.lineChart.context.rotate(-80 * Math.PI / 180);
        this.lineChart.context.textAlign = 'right';
        this.lineChart.context.fillText(moment(precence['startAt']['date']).format('D/M/YY'),0,3)
        this.lineChart.context.restore();
    }

    writeValue = (value, offsetY) => {
        this.lineChart.context.save();
        this.lineChart.context.translate(this.offsetX, offsetY);
        this.lineChart.context.textAlign = 'center';
        this.lineChart.context.fillText(value,0,-10)
        this.lineChart.context.restore();
    }

    drawLandmarks = () => {
        this.offsetX = this.lineChart.padding;
        this.offsetYEnd = this.lineChart.height - this.lineChart.footer;
        this.lineChart.context.save();
        this.lineChart.context.lineWidth = .2;
        this.lineChart.context.strokeStyle = this.markColor;
        this.lineChart.context.beginPath();
        this.lineChart.context.moveTo(this.offsetX, this.offsetYEnd)
        this.lineChart.context.lineTo(this.lineChart.width - this.lineChart.padding, this.offsetYEnd)
        this.lineChart.context.stroke();
        this.data.forEach((presence, index) => {
            this.lineChart.context.beginPath();
            this.lineChart.context.moveTo(this.offsetX, 0)
            this.lineChart.context.lineTo(this.offsetX, this.offsetYEnd)
            this.lineChart.context.stroke();
            this.offsetX += this.lineChart.gap;
        })
        this.lineChart.context.restore();
    }
}