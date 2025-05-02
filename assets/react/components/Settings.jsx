import React, { useState, useEffect } from 'react';
import ActionDropDownItem from '../components/ActionsDropDownItem';
import { getDataFromApi } from '../utils'

export default function Settings({api, handleRouteAction}) {
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
    
    return (
        <div className="dropdown sliders">
            <button className="dropdown-toggle" type="button" data-toggle="dropdown-settings"></button>
            <div className="dropdown-menu" data-target="dropdown-settings">
                <ul className="dropdown-body">
                    {parameters.map((action, id) => 
                        <ActionDropDownItem key={'parameter' + id} action={action} handleRouteAction={handleRouteAction}/> 
                    )}
                    {settings.map((action, id) => 
                        <ActionDropDownItem key={'action' + id} action={action} handleRouteAction={handleRouteAction}/>
                    )}
                    {messages.map((action, id) => 
                        <ActionDropDownItem key={'message' + id} action={action} handleRouteAction={handleRouteAction} />
                    )}
              </ul>
            </div>
        </div>
    )
}