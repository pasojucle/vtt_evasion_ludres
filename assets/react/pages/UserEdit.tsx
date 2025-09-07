import React from 'react';

import { useParams } from "react-router";

export default function UserEdit(): React.JSX.Element {

    let {id} = useParams();


    return (
        <div>
            <h2>User Edit</h2>
            <p>Secret</p>
            <p>{id}</p>
        </div>
    )
}