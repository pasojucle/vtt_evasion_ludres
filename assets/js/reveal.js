const options = {
    root: null,
    rootMargin: '0px',
    threshold: .1
  }
const targets = document.querySelectorAll('.reveal');

function handleIntersection(entries, options) {
    entries.map((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible')
        } else {
            entry.target.classList.remove('visible')
        }
    });
}

// const observer = new IntersectionObserver(handleIntersection);

// targets.forEach(target => observer.observe(target));

const observers = {};

targets.forEach(target => {
    const threshold = target.dataset.revealThreshold || 0.1;
    

    if (!observers[threshold]) {
        observers[threshold] = new IntersectionObserver(handleIntersection, {
            root: null,
            rootMargin: '0px',
            threshold: parseFloat(threshold)
        });
    }
console.log("observers[threshold]", observers[threshold])
    observers[threshold].observe(target);
});