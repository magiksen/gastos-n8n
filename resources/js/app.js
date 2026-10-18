import './bootstrap';
import '@hotwired/turbo';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('turbo:morph', () => {
    Alpine.destroyTree(document.body);
    Alpine.initTree(document.body);
});
