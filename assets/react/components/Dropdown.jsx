import React, { useState } from 'react';

export default function Dropdown({title, actions}) {


    const DropdownItem = (action) => {
        if (action.path) {
            return (
                <li>
                    <a href={action.path} className="dropdown-item" title={action.label}>
                        <i className={action.icon}></i> {action.label}
                    </a>
              </li>
            )
        }

        return (
            <li className="info"><i className={action.icon}></i> {action.label}</li>
        )
    }

    return (
        <div className="dropdown">
        <button className="dropdown-toggle" type="button" data-toggle="dropdown-{{ user.id }}"></button>
        <div className="dropdown-menu" data-target="dropdown-{{ user.id }}">
            <div className="dropdown-title">{title}</div>
                <ul className="dropdown-body">
                    {actions.map((action, id) => 
                        <DropdownItem key={id} action={action} />
                    )}
              </ul>
            </div>
        </div>
    )
}