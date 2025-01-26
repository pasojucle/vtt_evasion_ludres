import React, { useState, useEffect }  from 'react';
import {toString, getList, getListFiltered} from '../utils'

export default function ChoiceFilteredType({id, name, entityName, label, filters, mainList, className}) {
    const [list, setList] = useState([]);
    const [selectedValue, setSelectedValue] = useState('');

    useEffect(() => {
        const list = getList(`api_${entityName}_list`)
            .then((list) => setList(list))
    }, [])

    const listFiltered = () => {
        return getListFiltered(list, filters, mainList);
    }

    return (
        <div className={className}>
            <label htmlFor={id}>{label }</label>
            <select value={selectedValue} onChange={(event) => setSelectedValue(event.target.value)} className="form-control form-control-sm" name={name} id={id}>
                {listFiltered().map((entity) => 
                    <option key={entity.id} value={entity.id} disabled={entity.disabled} >{toString(entity)}</option>
                )}
            </select>
        </div>
    )
}