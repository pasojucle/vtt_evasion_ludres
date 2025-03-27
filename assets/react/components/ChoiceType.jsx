import React, { useState, useEffect }  from 'react';
import {toString, getData} from '../utils'

export default function ChoiceType({id, name, entityName, value, label, className}) {
    const [list, setList] = useState([]);
    const [selectedValue, setSelectedValue] = useState(value);

    useEffect(() => {
        const list = getData(`api_${entityName}_list`)
            .then((data) => setList(data.list))
    }, [])

    return (
        <div className={className}>
            <label htmlFor={id}>{label }</label>
            <select value={selectedValue} onChange={(event) => setSelectedValue(event.target.value)} className="form-control form-control-sm" name={name} id={id}>
                {list.map((entity) => 
                    <option key={entity.id} value={entity.id} >{toString(entity)}</option>
                )}
            </select>
        </div>
    )
}