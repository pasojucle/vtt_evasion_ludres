import { Controller } from '@hotwired/stimulus';
import moment from 'moment';


export default class extends Controller {
    static targets = ['canvas'];

    static values = {
        url: String,
        format: String,
    }

    connect() {
        this.display = {};
        this.lines = [];
        this.ratioY = 0;
        this.delay = 1000 / 30;
        this.loop = 0;
        this.startLoop = 10;
        this.base = 100;
        this.interval = null;
        this.ctx = this.canvasTarget.getContext("2d");
        this.loadData();
    }

    disconnect() {
        if (this.interval) {
            clearInterval(this.interval);
        }
    }

    createCanvas() {
        const canvas = document.createElement("canvas");
        canvas.classList.add("line-chart");

        return canvas;
    }

    async loadData() {
        try {
            const response = await fetch(this.urlValue);
            const json = await response.json();
            if (json.membersPrecences) {
                json.membersPrecences.forEach((presences) => {
                    this.lines.push(new Line(this, presences));
                });
                
                this.setFormat();
                this.run();
            }
        } catch (error) {
            console.error(`Erreur sur le canvas ${this.element.id} :`, error);
        }
    }

    addLine = (presences) => {
        this.lines.push(new Line(this, presences));
    }
    setFormat() {
        if (this.formatValue === 'card') {
            this.width = this.canvasTarget.parentElement.offsetWidth;
            this.height = 200;
        }
        this.canvasTarget.width = this.width;
        this.canvasTarget.height = this.height;
        this.padding = 10;
        this.footer = 50;
        this.gap = (this.width - this.padding * 2 ) / (this.lines[0].data.length - 1);
        this.textPosition = this.height - this.footer + this.padding
    }
    next () {
        this.loop++;
        if (this.startLoop < this.loop) {
            this.ratioY = Math.pow(this.loop - this.startLoop, 2); 
        }
    }
    frameDelay ()  {
        this.delay = 1000 / 30;
    }
    run ()  {
        this.interval = setInterval(() => {
            this.ctx.clearRect(0, 0, this.width, this.height);
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
    draw ()  {
        this.drawLine();
        this.drawLandmarks();
    }
    drawLine ()  {
        this.offsetX = this.lineChart.padding;
        this.lineChart.ctx.lineWidth = 2.5;
        this.lineChart.ctx.beginPath();
        this.lineChart.ctx.strokeStyle = this.color;
        this.data.forEach((presence, index) => {
            
            const value = parseInt(presence[1]) * this.lineChart.ratioY / this.lineChart.base;
            this.drawItem(value, index);
            this.writeDate(presence);
            this.offsetX += this.lineChart.gap;
        })
        this.lineChart.ctx.stroke();
    }
    drawItem = (value, index) => {
        const offsetY = this.lineChart.height - value * 2 - 50;
        this.writeValue(value, offsetY)
        if (0 < index) {
            this.lineChart.ctx.lineTo(this.offsetX, offsetY );
            return;
        }
        
        this.lineChart.ctx.moveTo(this.offsetX, offsetY)
    }
    writeDate = (precence) => {
        this.lineChart.ctx.save();
        this.lineChart.ctx.translate(this.offsetX, this.lineChart.textPosition);
        this.lineChart.ctx.rotate(-80 * Math.PI / 180);
        this.lineChart.ctx.textAlign = 'right';
        this.lineChart.ctx.fillText(moment(precence['startAt']['date']).format('D/M/YY'),0,3)
        this.lineChart.ctx.restore();
    }

    writeValue = (value, offsetY) => {
        this.lineChart.ctx.save();
        this.lineChart.ctx.translate(this.offsetX, offsetY);
        this.lineChart.ctx.textAlign = 'center';
        this.lineChart.ctx.fillText(value,0,-10)
        this.lineChart.ctx.restore();
    }

    drawLandmarks ()  {
        this.offsetX = this.lineChart.padding;
        this.offsetYEnd = this.lineChart.height - this.lineChart.footer;
        this.lineChart.ctx.save();
        this.lineChart.ctx.lineWidth = .2;
        this.lineChart.ctx.strokeStyle = this.markColor;
        this.lineChart.ctx.beginPath();
        this.lineChart.ctx.moveTo(this.offsetX, this.offsetYEnd)
        this.lineChart.ctx.lineTo(this.lineChart.width - this.lineChart.padding, this.offsetYEnd)
        this.lineChart.ctx.stroke();
        this.data.forEach((presence, index) => {
            this.lineChart.ctx.beginPath();
            this.lineChart.ctx.moveTo(this.offsetX, 0)
            this.lineChart.ctx.lineTo(this.offsetX, this.offsetYEnd)
            this.lineChart.ctx.stroke();
            this.offsetX += this.lineChart.gap;
        })
        this.lineChart.ctx.restore();
    }
}