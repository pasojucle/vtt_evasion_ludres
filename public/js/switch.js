$(document).ready(function(){
    document
        .querySelectorAll('.switch input[type="checkbox"]')
        .forEach(btn => btn.addEventListener("change", handleSwitch));
});

function handleSwitch(event) {
    const swicthLabel = document.querySelector('label[for="'+event.target.id+'"]');
    if (event.target.dataset.switchOn && event.target.dataset.switchOff ) {
       swicthLabel.innerHTML =  (event.target.checked) ? event.target.dataset.switchOn : event.target.dataset.switchOff; 
    }
}

