import Routing from 'fos-router';
import ChoiceType from './components/ChoiceType.jsx';
import ChoiceFilteredType from './components/ChoiceFilteredType.jsx';
import Ckeditor from './controllers/Ckeditor.jsx';
import HiddenType from './components/HiddenType.jsx'

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

export const formElement = (component, key, filters={}, mainList=[]) => {
    console.log('component', component)
    let className = 'form-group';
    if (component.row_attr) {
        className = `${className} ${component.row_attr.class}`
    }
    switch (component.name) {
        case 'HiddenType':
            return (<HiddenType key={key} id={component.props.id} name={component.props.name} value={component.props.value}/>)
            break;
        case 'ChoiceType':
            return (
                <div key={key} className={className}>
                    <ChoiceType id={component.props.id} name={component.props.name} entityName={component.props.entityName} value={component.props.value} label={component.props.label} />
                </div>
                )
            break;
        case 'Ckeditor':
            return (
                <div key={key} className={className}>
                    <Ckeditor id={component.props.id} name={component.props.name} value={component.props.value} label={component.props.label} upload_url={component.props.upload_url} toolbar={component.props.toolbar} environment={component.props.environment} />
                </div>
                )
            break;
        case 'ChoiceFilteredType':
            return (
                <div key={key} className={className}>
                    <ChoiceFilteredType id={component.props.id} name={component.props.name} value={component.props.value} label={component.props.label} entityName={component.props.entityName} filters={filters} mainList={mainList}/>
                </div>
                )
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
