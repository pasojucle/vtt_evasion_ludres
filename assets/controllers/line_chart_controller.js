import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['canvas'];

    static values = {
        url: String,
        data: Array,
        scaleY: Number,
    }

    connect() {
        this.lines = [];
        this.ratioY = 0;
        this.delay = 1000 / 30;
        this.loop = 0;
        this.startLoop = 10;
        this.base = 100;
        this.height = 200;
        this.interval = null;
        this.chartData = [];
        this.ctx = this.canvasTarget.getContext("2d");
        this.scaleY = this.hasScaleYValue ? this.scaleYValue : 1;

        this.resizeObserver = new ResizeObserver((entries) => {
            for (const entry of entries) {
                const width = entry.contentRect.width;
                if (width > 0) {
                    this.width = width;
                    this.onVisibleOrResize();
                }
            }
        });

        if (this.canvasTarget.parentElement) {
            this.resizeObserver.observe(this.canvasTarget.parentElement);
        }

        this.loadData();
    }

    disconnect() {
        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }
        this.clear();
    }

    async loadData() {
        if (this.hasUrlValue) {
            await this.fetchData();
        } else if (this.hasDataValue) {
            this.chartData = this.dataValue;
            this.renderChart();
        }
    }

    async fetchData() {
        try {
            const response = await fetch(this.urlValue);
            const json = await response.json();
            if (json.items) {
                this.chartData = json.items;
                this.renderChart();
            }
        } catch (error) {
            console.error(`Erreur sur le canvas ${this.element.id} :`, error);
        }
    }

    onVisibleOrResize() {
        if (this.chartData.length > 0) {
            this.renderChart();
        }
    }

    clear() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
        this.loop = 0;
        this.ratioY = 0;
    }

    renderChart() {
        if (!this.chartData || this.chartData.length === 0 || !this.width) return;

        this.clear();

        this.lines = [];
        this.chartData.forEach((item) => {
            this.lines.push(new Line(this, item));
        });

        this.setFormat();
        this.run();
    }

    setFormat() {
        this.canvasTarget.width = this.width;
        this.canvasTarget.height = this.height;
        this.padding = 10;
        this.footer = 50;

        const dataLength = this.lines[0]?.data?.length || 1;
        this.gap = (this.width - this.padding * 2) / Math.max(1, dataLength - 1);
        this.textPosition = this.height - this.footer + this.padding;
    }

    next() {
        this.loop++;
        if (this.startLoop < this.loop) {
            this.ratioY = Math.pow(this.loop - this.startLoop, 2); 
        }
    }

    run() {
        this.interval = setInterval(() => {
            this.ctx.clearRect(0, 0, this.width, this.height);
            this.lines.forEach((line) => {
                line.draw();
            });

            this.next();

            if (this.base < this.ratioY) {
                clearInterval(this.interval);
                this.interval = null;
            }
        }, this.delay);
    }
}

class Line {
    offsetX = 0;
    constructor(lineChart, item) {
        this.lineChart = lineChart;
        this.data = item.points;
        this.color = item.lineColor;
        this.markColor = 'rgba(0,0,0,0.7)';
    }

    draw() {
        this.drawLine();
        this.drawLandmarks();
    }

    drawLine() {
        this.offsetX = this.lineChart.padding;
        this.lineChart.ctx.lineWidth = 2.5;
        this.lineChart.ctx.beginPath();
        this.lineChart.ctx.strokeStyle = this.color;
        
        this.data.forEach((presence, index) => {
            const value = parseInt(presence.total) * this.lineChart.ratioY / this.lineChart.base;
            this.drawItem(value, index);
            this.writeDate(presence);
            this.offsetX += this.lineChart.gap;
        });

        this.lineChart.ctx.stroke();
    }

    drawItem = (value, index) => {
        const offsetY = this.lineChart.height - value * this.lineChart.scaleY * 2 - 50;
        if (this.lineChart.ratioY === 0 || this.lineChart.ratioY >= 100) {
            this.writeValue(value, offsetY);
        }

        if (index > 0) {
            this.lineChart.ctx.lineTo(this.offsetX, offsetY);
            return;
        }

        this.lineChart.ctx.moveTo(this.offsetX, offsetY);
    }

    writeDate = (presence) => {
        this.lineChart.ctx.save();
        this.lineChart.ctx.translate(this.offsetX, this.lineChart.textPosition);
        this.lineChart.ctx.rotate(-80 * Math.PI / 180);
        this.lineChart.ctx.textAlign = 'right';
        this.lineChart.ctx.fillText(presence['label'], 0, 3);
        this.lineChart.ctx.restore();
    }

    writeValue = (value, offsetY) => {
        this.lineChart.ctx.save();
        this.lineChart.ctx.translate(this.offsetX, offsetY);
        this.lineChart.ctx.textAlign = 'center';
        this.lineChart.ctx.fillText(Math.round(value), 0, -10);
        this.lineChart.ctx.restore();
    }

    drawLandmarks() {
        this.offsetX = this.lineChart.padding;
        this.offsetYEnd = this.lineChart.height - this.lineChart.footer;
        this.lineChart.ctx.save();
        this.lineChart.ctx.lineWidth = 0.2;
        this.lineChart.ctx.strokeStyle = this.markColor;
        this.lineChart.ctx.beginPath();
        this.lineChart.ctx.moveTo(this.offsetX, this.offsetYEnd);
        this.lineChart.ctx.lineTo(this.lineChart.width - this.lineChart.padding, this.offsetYEnd);
        this.lineChart.ctx.stroke();

        this.data.forEach(() => {
            this.lineChart.ctx.beginPath();
            this.lineChart.ctx.moveTo(this.offsetX, 0);
            this.lineChart.ctx.lineTo(this.offsetX, this.offsetYEnd);
            this.lineChart.ctx.stroke();
            this.offsetX += this.lineChart.gap;
        });

        this.lineChart.ctx.restore();
    }
}