import React, { useState }  from 'react';

export default function TextType({id, name, value, disabled, className}) {

    const [textValue, setTextValue] = useState(value);

    const handleChange = (value) => {
        setTextValue(value);
    }

    return (
        <div className={className}>
            <input className="form-control form-control-sm" type="text" id={id}  name={name} value={textValue} disabled={disabled} onChange={event => handleChange(event.target.value)}/>
        </div>
    )
}