import React from 'react';

export default function HiddenType({id, name, value}) {

    return (
        <div>
            <input type="hidden" id={id} name={name} value={value}/>
        </div>
    )
}