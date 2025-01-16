import Routing from 'fos-router';

export const toString = (entity) => {
    let string;
    switch(true) {
        case undefined !== entity.title:
            string = entity.title;
            break;
        case undefined !== entity.content:
            const htmlElement = document.createRange().createContextualFragment(entity.content);
            string = htmlElement.firstChild.innerText;
            break;
        default:
            string = entity.name;
    }
    return string;
  }

  export const getList = async(route, params={}) => {
    const promise = await fetch(Routing.generate(route, params));

    const result = await promise.json();

    return result.list;
  }