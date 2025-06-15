import React, { useRef, useState } from 'react';
import { Link } from "react-router-dom";
import { useAuth } from "../hooks/useAuth";
import { useModal} from '../hooks/useModal';

export default function Protecteds() {

    const { token } = useAuth();
    const { show } = useModal();

    if (token) {
        return (
            <>
                <Link className="block mt-4 lg:inline-block lg:mt-0 hover:text-white px-4 py-2 rounded hover:bg-blue-700 mr-2" to="/article/add">Ajouter un article</Link>
                <Link className="block mt-4 lg:inline-block lg:mt-0 hover:text-white px-4 py-2 rounded hover:bg-blue-700 mr-2" to="/users">Ajouter un utilisateur</Link>
                <button onClick={() => show('Paramètres', 'parameters', 'sm', 'parameters')}
                    className="block mt-4 lg:inline-block lg:mt-0 hover:text-white px-4 py-2 rounded hover:bg-blue-700 mr-2">Paramètres
                </button>
            </>
        )
    }
}