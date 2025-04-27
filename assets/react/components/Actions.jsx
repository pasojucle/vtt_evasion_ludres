import React, { useState, useEffect } from 'react';
import { getDataFromApi } from '../utils'

export default function Actions({api}) {
    const [actions, setActions] = useState([]);

    useEffect(() => {
        getDataFromApi(api.actions)
            .then((data) => {
                console.log('actions', data)
                setActions(data);
        })
    }, [])

    const DropdownItem = ({action}) => {
        if (action.url) {
            return (
                <li>
                    <a href={action.url} className="dropdown-item" title={action.label} data-toggle="modal" data-type="primary">
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
            <button className="dropdown-toggle" type="button" data-toggle="dropdown-settings"></button>
            <div className="dropdown-menu" data-target="dropdown-settings">
                <ul className="dropdown-body">
                    {actions.map((action, id) => 
                        <DropdownItem key={'action' + id} action={action} />
                    )}
              </ul>
            </div>
        </div>
    )
}