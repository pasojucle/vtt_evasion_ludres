import React from 'react';

export default function TextType({id, name, value, disabled, className}) {

    return (
        <div className={className}>
            <input className="form-control form-control-sm" type="text" id={id}  name={name} value={value} disabled={disabled}/>
        </div>
    )
}