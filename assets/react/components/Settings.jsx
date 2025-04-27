import React, { useState, useEffect } from 'react';
import { getDataFromApi } from '../utils'

export default function Settings({api}) {
    const [messages, setMessages] = useState([]);
    const [parameters, setParameters] = useState([]);
    const [settings, setSettings] = useState([]);

    useEffect(() => {
        getDataFromApi(api.messages)
            .then((data) => {
                console.log('messages', data)
                setMessages(data);
        })
        getDataFromApi(api.parameters)
            .then((data) => {
                console.log('parameters', data)
                setParameters(data);
        })
        getDataFromApi(api.settings)
            .then((data) => {
                console.log('settings', data)
                setSettings(data);
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
        <div className="dropdown sliders">
            <button className="dropdown-toggle" type="button" data-toggle="dropdown-settings"></button>
            <div className="dropdown-menu" data-target="dropdown-settings">
                <ul className="dropdown-body">
                    {parameters.map((action, id) => 
                        <DropdownItem key={'parameter' + id} action={action} />
                    )}
                    {settings.map((action, id) => 
                        <DropdownItem key={'action' + id} action={action} />
                    )}
                    {messages.map((action, id) => 
                        <DropdownItem key={'message' + id} action={action} />
                    )}
              </ul>
            </div>
        </div>
    )
}