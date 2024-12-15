import { previewFile } from './input-file';

const addDeleteLink = () => {
    const collectionItems = document.querySelectorAll('.collection_container .form-group-collection:not(.not-deleted)');
    collectionItems.forEach((item) => {
        if (item.querySelectorAll('input:disabled').length < 1) {
            addTagFormDeleteLink(item);
        } 
    })
}

const initAddItemLink = () => {
  document.querySelectorAll('.add_item_link, .add-item-file').forEach(btn => btn.addEventListener("click", addFormToCollection));
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

  if (e.currentTarget.classList.contains('add-item-file')) {
    const inputFile = collectionHolder.lastChild.querySelector('input[type="file"]');
    inputFile.click();
    inputFile.addEventListener('change', (event) => {
      const image = document.createElement('IMG');
      previewFile(event)
    });    
  }

  container.dataset.index++;
  addTagFormDeleteLink(collectionHolder.lastChild);
};

const addTagFormDeleteLink = (itemForm) => {
  const removeFormButton = document.createElement('button');
  removeFormButton.classList.add('btn', 'btn-xs', 'btn-danger', 'col-md-1');
  removeFormButton.innerHTML ='<i class="fas fa-times"></i>';
  const element = (itemForm.classList.contains('form-group-collection'))
    ? itemForm
    : itemForm.querySelector('.form-group-collection');

  if (element) {
    element.append(removeFormButton)
  }
  removeFormButton.addEventListener('click', (e) => {
      e.preventDefault()
      itemForm.remove();
  });
}

export { addDeleteLink, initAddItemLink }