import React, { useState }  from 'react';
import { formElement } from '../utils.js';

export default function CollectionType({id, children, className}) {

    return (
        <div id={id} className={className}>
            {children.map((child, key) => 
                <div key={key} className="row form-group form-group-collection">
                    {child.map((component,key) => 
                        formElement(component, key, true)
                    )}
                </div>
            )}
        </div>
    )
}