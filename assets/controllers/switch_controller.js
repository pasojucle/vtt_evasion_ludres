import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["label", "input"];

    static values = {
        on: String,
        off: String,
    }

    connect() {
        console.log("switch_controller")
    }

    change(event) {
        console.log('switch_controller change', this.inputTarget, this.onValue ,  this.offValueOff )
        if (this.hasOnValue && this.hasOffValue) {
            this.labelTarget.innerHTML =  (this.inputTarget.checked) ? this.onValue : this.offValue; 
        }
    }
}