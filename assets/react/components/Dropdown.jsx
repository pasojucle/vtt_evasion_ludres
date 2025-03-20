import React, { useState } from 'react';

export default function Dropdown({title, actions}) {


    const DropdownItem = (action) => {
        if (action.path) {
            return (
                <li>
                    <a href={action.path} class="dropdown-item" title={action.label}>
                        <i className={action.icon}></i> {action.label}
                    </a>
              </li>
            )
        }

        return (
            <li class="info"><i className={action.icon}></i> {action.label}</li>
        )
    }

    return (
        <div class="dropdown">
        <button class="dropdown-toggle" type="button" data-toggle="dropdown-{{ user.id }}"></button>
        <div class="dropdown-menu" data-target="dropdown-{{ user.id }}">
            <div class="dropdown-title">{title}</div>
                <ul class="dropdown-body">
                    {actions.map((action, id) => 
                        <DropdownItem key={id} action={action} />
                    )}
              </ul>
            </div>
        </div>
    )
}