import './bootstrap';
import '@hotwired/turbo';
import Alpine from 'alpinejs';
import { registrarTema } from './tema';

window.Alpine = Alpine;

registrarTema(Alpine);
Alpine.start();

const reiniciarAlpine = () => {
    Alpine.destroyTree(document.body);
    document.body.querySelectorAll('[x-data]').forEach((elemento) => delete elemento._x_dataStack);
    Alpine.initTree(document.body);
};

document.addEventListener('turbo:morph', reiniciarAlpine);
