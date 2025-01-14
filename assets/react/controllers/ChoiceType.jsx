import Reactt, { useState, useEffect }  from 'react';
import Routing from 'fos-router';
import {toString} from '../../js/utils'

export default function ChoiceType({id, name, className, initialValue, label}) {
    const [list, setList] = useState([]);
    const [value, setValue] = useState(initialValue);

    useEffect(() => {
        fetch(Routing.generate(`api_${className}_list`), {
            method: "GET", 
        })
        .then(response => response.json())
        .then(json => {
            console.log('json', json)
            setList(json.list);
        });
    }, [])

    return (
        <div className='form-group'>
            <label htmlFor={id}>{label }</label>
            <select value={value} onChange={(event) => setValue(event.target.value)} className="form-control form-control-sm" name={name} id={id}>
                {list.map((entity) => 
                    <option key={entity.id} value={entity.id} >{toString(entity)}</option>
                )}
            </select>
        </div>
    )
}