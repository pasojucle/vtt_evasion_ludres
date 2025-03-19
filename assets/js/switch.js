document.addEventListener('DOMContentLoaded', () => {
    switchEventListener();
});

export const switchEventListener = () => {
    document.querySelectorAll('.switch input[type="checkbox"]').forEach((element) => {
        element.addEventListener('change', handleSwitch)
    });
}

const handleSwitch = (event) => {
    const swicthLabel = document.querySelector('label[for="'+event.target.id+'"]');
    if (event.target.dataset.switchOn && event.target.dataset.switchOff ) {
       swicthLabel.innerHTML =  (event.target.checked) ? event.target.dataset.switchOn : event.target.dataset.switchOff; 
    }
}

