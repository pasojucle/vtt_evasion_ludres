import React from 'react';

import { useParams } from "react-router";

export default function UserEdit() {

    let {id} = useParams();

    console.log('section', id)

    return (
        <div>
            <h2>User Edit</h2>
            <p>Secret</p>
            <p>{id}</p>
        </div>
    )
}