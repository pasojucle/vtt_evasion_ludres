import React from 'react';

import { useParams } from "react-router";

export default function Section() {

    let {id} = useParams();

    console.log('section', id)
    return (
        <div>
            <p>{id}</p>
        </div>
    )
}