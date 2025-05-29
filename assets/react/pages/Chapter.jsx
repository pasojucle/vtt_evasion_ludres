import React from 'react';

import { useParams } from "react-router";

export default function Chapter() {

    let {id} = useParams();

    console.log('chapter', id)
    return (
        <div>
            <p>{id}</p>
        </div>
    )
}