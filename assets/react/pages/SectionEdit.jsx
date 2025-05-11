import React from 'react';

import { useParams } from "react-router";
import { useAuth } from "../hooks/useAuth";

export default function SectionEdit() {

    let {id} = useParams();

    console.log('section', id)

    const { user } = useAuth();

    if (!user) {
      return <Navigate to="/" />;
    }

    return (
        <div>
            <h2>Edit</h2>
            <p>Secret</p>
            <p>{id}</p>
        </div>
    )
}