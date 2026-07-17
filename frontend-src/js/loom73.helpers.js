(function () {

    const elements = document.querySelectorAll('[data-inview]');

    if (!elements.length) return;

    if (!('IntersectionObserver' in window)) {
        elements.forEach(element => {
            element.classList.add(element.dataset.inview);
        });
        return;
    }

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const className = entry.target.dataset.inview;
            if (className) {
                entry.target.classList.add(className);
            }
            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.25
    });
    elements.forEach(element => {
        observer.observe(element);
    });
})();