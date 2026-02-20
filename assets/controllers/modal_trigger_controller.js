import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
    }
    
    async deleteUser(event) {
        event.preventDefault();
        const userId = this.element.elements['user_search[user]'].value
        const url = this.urlValue.replace('0', userId);

        const response = await fetch(url);
        const content = await response.text();
        this.dispatch("openWithContent", { 
            prefix: "modal",
            detail: { content: content } 
        })
    }
}