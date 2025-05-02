import React from 'react';
import {emailToClipboard} from '../../js/clipboard.js';

export default function ActionsDropDownItem({action, handleRouteAction}) {
    const handleClick = (event, action) => {

        console.log(action.url);
        switch(action.onClick) {
            case 'emailToClipboard':
                emailToClipboard(event);
                break;
            case 'toggleModal':
                event.preventDefault();
                console.log(action.url);
                handleRouteAction(action.url);
        }
    }

    const DropdownItem = ({action}) => {
        if (action.url) {
            return (
                <li>
                    <a href={action.url} className="dropdown-item" title={action.label} onClick={(event) => {handleClick(event, action)}}>
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
        <DropdownItem action={action} />
    )
}