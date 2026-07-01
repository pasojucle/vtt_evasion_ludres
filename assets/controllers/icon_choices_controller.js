import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["gauge", "input"];
    static values = {
        value: String,
        choices: Array,
        route: String,
        start: Number,
        end: Number,
    }

    connect() {
        const index = this.choicesValue.findIndex((choice) => choice === this.valueValue);
        const startIndex = Math.max(0, index - 25); 
        const endIndex = index + 25;

        const choices = this.choicesValue.slice(startIndex, endIndex);
        
        this.startValue = startIndex;
        this.endValue = endIndex;

        this.stream(choices, 'update');
    }

    up() {
        const startIndex = Math.max(0, this.startValue - 50); 
        const choices = this.choicesValue.slice(startIndex, this.startValue);
        
        if (choices.length === 0) return; 

        this.startValue = startIndex;
        this.stream(choices, 'prepend');
    }

    down() {
        const endIndex = Math.min(this.choicesValue.length, this.endValue + 50);
        const choices = this.choicesValue.slice(this.endValue, endIndex);

        if (choices.length === 0) return;

        this.endValue = endIndex;
        this.stream(choices, 'append');
    }

    stream(choices, action) {
        const formData = new FormData();
        formData.append('choices', JSON.stringify(choices));
        formData.append('value', this.valueValue);
        formData.append('action', action);
        fetch(this.routeValue, { 
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'text/vnd.turbo-stream.html'
            }
        })
        .then(response => response.text())
        .then(html => {
            Turbo.renderStreamMessage(html);
            this.gaugeTarget.innerText = this.endValue - this.startValue
        });
    }   
    
    select(event) {
        console.log("select", event.currentTarget, event.currentTarget.dataset.iconItem)
        const target = event.currentTarget;
        this.inputTarget.value = target.dataset.iconItem;
        document.querySelectorAll("[data-icon-item]").forEach(item => {
            item.classList.remove("bg-slate-300");
        })
        target.classList.add("bg-slate-300");
    }
}