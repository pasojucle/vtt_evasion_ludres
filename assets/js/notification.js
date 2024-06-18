document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.badge.novelty').forEach((element) => {
        console.log('route', element.dataset.route)
        if (element.dataset.route !== undefined) {
            hasNews(element.dataset.route)
        }
    })

});

const hasNews = async(route) => {
    await fetch( Routing.generate(route))
    .then((response) => {
        if (response.status !== 500) {
            return response.json();
        }
        throw new Error('Something went wrong.');    
    })
    .then((json)=> {
        
        if (json.hasNewItem) {
            console.log('json', json)
            const element = document.querySelector(`[data-route="${route}"]`);
            element.classList.remove('hidden');
            if (element.parentElement.classList.contains('nav-sub')) {
                element.closest('li.nav-bar-xs').querySelector('.badge.novelty').classList.remove('hidden');
            }
        }
        
    });
}