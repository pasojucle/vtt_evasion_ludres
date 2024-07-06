

const addDeleteLink = () => {
    const collectionItems = document.querySelectorAll('.collection_container .form-group-collection:not(.not-deleted)');
    collectionItems.forEach((item) => {
        if ($(item).find('input:disabled').length < 1) {
            addTagFormDeleteLink(item);
        } 
    })
}

const initAddItemLink = () => {
    document.querySelectorAll('.add_item_link').forEach(btn => btn.addEventListener("click", addFormToCollection));
}


const addFormToCollection = (e) => {
    const collectionHolder = document.querySelector('#' + e.currentTarget.dataset.collectionHolderClass);
    const container = collectionHolder.closest('.collection_container');
    const html = container
      .dataset
      .prototype
      .replace(
        /__name__/g,
        container.dataset.index
      );

    const item = document.createRange().createContextualFragment(html)
  
    collectionHolder.appendChild(item);
  
    container.dataset.index++;
    addTagFormDeleteLink(collectionHolder.lastChild);
  };

  const addTagFormDeleteLink = (itemForm) => {
    const removeFormButton = document.createElement('button');
    removeFormButton.classList.add('btn', 'btn-xs', 'btn-danger', 'col-md-1');
    removeFormButton.innerHTML ='<i class="fas fa-times"></i>';
    $(itemForm).append(removeFormButton);

    removeFormButton.addEventListener('click', (e) => {
        e.preventDefault()
        itemForm.remove();
    });
}

export { addDeleteLink, initAddItemLink }