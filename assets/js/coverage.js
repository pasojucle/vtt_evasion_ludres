let optionsEl;

document.addEventListener("DOMContentLoaded", (event) => {
    optionsEl = document.querySelectorAll('[name="user[lastLicence][options][]"')
    optionsEl.forEach((element) => {
        element.addEventListener('click', handleClick)
    })
});

const handleClick = (event) => {
    if (event.target.value === 'no_additional_option') {
        Array.from(optionsEl).filter((el) => el.value !== 'no_additional_option').forEach((element) => {
            element.checked = false;
        })
        return;
    }
    Array.from(optionsEl).find((el) => el.value === 'no_additional_option').checked = false;
}