import Alpine from 'alpinejs';
import './task-editor';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('keydown', function (e) {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        const input = document.getElementById('search-input');
        if (input) input.focus();
    }
});
