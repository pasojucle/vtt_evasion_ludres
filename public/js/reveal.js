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

const observer = new IntersectionObserver(handleIntersection);

targets.forEach(target => observer.observe(target));