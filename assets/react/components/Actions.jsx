import React, { useState, useEffect } from 'react';
import ActionDropDownItem from '../components/ActionsDropDownItem';
import { getDataFromApi } from '../utils';

export default function Actions({api, handleRouteAction}) {
    const [actions, setActions] = useState([]);

    useEffect(() => {
        getDataFromApi(api.actions)
            .then((data) => {
                console.log('actions', data)
                setActions(data);
        })
    }, [])

    return (
        <div className="dropdown">
            <button className="dropdown-toggle" type="button" data-toggle="dropdown-settings"></button>
            <div className="dropdown-menu" data-target="dropdown-settings">
                <ul className="dropdown-body">
                    {actions.map((action, id) => 
                        <ActionDropDownItem key={'action' + id} action={action} handleRouteAction={handleRouteAction}/>
                    )}
              </ul>
            </div>
        </div>
    )
}