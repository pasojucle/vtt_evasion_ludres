import React from 'react';

export default function HiddenType({name, value}) {

    return (
        <div>
            <input type="hidden" name={name} value={value}/>
        </div>
    )
}