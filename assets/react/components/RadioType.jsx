import React, { useState }  from 'react';

export default function RadioType({id, name, label, choices, value, className}) {
    const [picked, setPicked] = useState(value);

    const getBackground = (choice) => {
        if (picked === choice.value) {
            return  choice.color;
        }
        return 'unset';
    }

    return (
        <div className={className}>
            <label htmlFor={id}>{label }</label>
            {choices.map((choice) => 
                <div className="radio-group-vue" key={choice.value}>
                    <input type="radio" name={name} id={choice.id} value={choice.value} onClick={(event) => setPicked(event.target.value)} checked={picked === choice.value}/>
                    <label className="label" htmlFor={choice.id} style={{'backgroundColor': getBackground(choice)}}>{ choice.label }</label>
                </div>
            )}
        </div>
    )
}