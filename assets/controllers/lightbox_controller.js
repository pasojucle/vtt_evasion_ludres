import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['mainImage'];

    switch(event) {
        event.preventDefault();
        
        const newSrc = event.currentTarget.getAttribute('data-lightbox-src');
        const altText = event.currentTarget.getAttribute('alt');

        if (this.hasMainImageTarget && newSrc) {
            this.mainImageTarget.src = newSrc;
            this.mainImageTarget.alt = altText;

            this.element.querySelectorAll('[data-lightbox-src]').forEach(img => img.classList.add('opacity-60'));
            event.currentTarget.classList.remove('opacity-60');
        }
    }
}