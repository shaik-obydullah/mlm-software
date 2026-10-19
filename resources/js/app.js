import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.copy = (text) => navigator.clipboard.writeText(text);

Alpine.store('toast', {
    show: false,
    type: 'success',
    message: '',
    timeout: null,
    notify(message, type = 'success') {
        this.message = message;
        this.type = type;
        this.show = true;
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => (this.show = false), 3500);
    },
});

document.addEventListener('alpine:init', () => {
    Alpine.data('treeNode', (node, depth) => ({
        node,
        depth,
        expanded: depth < 2,
        toggle() {
            this.expanded = !this.expanded;
        },
    }));
});

Alpine.start();
