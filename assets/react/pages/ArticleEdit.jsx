import React from 'react';

import { useParams } from "react-router";

export default function ArticleEdit() {

    let {id} = useParams();

    console.log('section', id)

    return (
        <div>
            <h2>Article Edit</h2>
            <p>Secret</p>
            <p>{id}</p>
        </div>
    )
}