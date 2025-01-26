import Routing from 'fos-router';
import ChoiceType from './components/ChoiceType.jsx';
import ChoiceFilteredType from './components/ChoiceFilteredType.jsx';
import Ckeditor from './controllers/Ckeditor.jsx';
import HiddenType from './components/HiddenType.jsx'
import CollectionType from './components/CollectionType.jsx';
import RadioType from './components/RadioType.jsx';
import TextType from './components/TextType.jsx';

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

export const resolve = (path, obj) => {
    return path.split('.').reduce(function(prev, curr) {
        return prev ? prev[curr] : null
    }, obj || self)
}

export const getList = async(route, params={}) => {
    const promise = await fetch(Routing.generate(route, params));

    const result = await promise.json();

    return result.list;
}

export const formElement = (component, key, isCollection, filters={}, mainList=[]) => {
    const className = [];
    if (!isCollection) {
        className.push('form-group');
    }
    if (component.row_attr) {
        className.push(component.row_attr.class);
    }
    const classNameStr = className.join(' ');
    switch (component.name) {
        case 'HiddenType':
            return <HiddenType key={key} id={component.props.id} name={component.props.name} value={component.props.value}/>
            break;
        case 'ChoiceType':
            return <ChoiceType key={key} className={classNameStr} id={component.props.id} name={component.props.name} entityName={component.props.entityName} value={component.props.value} label={component.props.label} />
            break;
        case 'Ckeditor':
            return <Ckeditor key={key} className={classNameStr} id={component.props.id} name={component.props.name} value={component.props.value} label={component.props.label} upload_url={component.props.upload_url} toolbar={component.props.toolbar} environment={component.props.environment} />
            break;
        case 'ChoiceFilteredType':
            return <ChoiceFilteredType key={key} className={classNameStr} id={component.props.id} name={component.props.name} value={component.props.value} label={component.props.label} entityName={component.props.entityName} filters={filters} mainList={mainList}/>
            break;
        case 'RadioType':
                return <RadioType key={key} className={classNameStr} id={component.props.id} name={component.props.name} value={component.props.value} label={component.props.label} choices={component.props.choices}/>
            break;
        case 'CollectionType':
            return  <CollectionType key={key} className={classNameStr} id={component.props.id} children={component.children}/>
    
            break;
        case 'TextType':
            return <TextType key={key} className={classNameStr} id={component.props.id} name={component.props.name} value={component.props.value} disabled={component.props.disabled}/>
            break;
    }

}

export const getListFiltered = (list, filters, mainList) => {
    for(let i in list) {
        const result = mainList.find((itemSelected) =>  itemSelected.id === list[i].id)
        list[i]['disabled'] = undefined !== result;
    };

    Object.entries(filters).forEach(([name, value]) => {
        if (null !== value) {
        list = ('name' === name) 
            ? list.filter(item => resolve(name, item) === value)
            : list.filter(item => resolve(name, item).id === value);
        }
    })

    return list;
}

export const updateList = (list, data) => {
    const index = list.findIndex(item => {
        return (data.value.id === item.id)
    })
    switch(true) {
        case -1 === index:
            list.push(data.value);
            break;
        case data.deleted:
            list.splice(index, 1);
            break;
        default:
            list.splice(index, 1, data.value)
    }
    return list
}
