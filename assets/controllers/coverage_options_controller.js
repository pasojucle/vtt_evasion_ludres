import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['option'];

    change(event) {
        if (event.target.value === 'no_additional_option') {
            this.optionTargets.filter((el) => el.value !== 'no_additional_option').forEach((element) => {
                element.checked = false;
            })
            return;
        }
        this.optionTargets.find((el) => el.value === 'no_additional_option').checked = false;
    }
}