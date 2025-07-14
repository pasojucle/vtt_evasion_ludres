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
                <button onClick={() => show('Ajouter un article', 'article', 'lg')}
                    className="block mt-4 lg:inline-block lg:mt-0 hover:text-white px-4 py-2 rounded hover:bg-blue-700 mr-2">Ajouter un article
                </button>
                <button onClick={() => show('Ajouter un utilisateur', 'user', 'md')}
                    className="block mt-4 lg:inline-block lg:mt-0 hover:text-white px-4 py-2 rounded hover:bg-blue-700 mr-2">Ajouter un utilisateur
                </button>
            </>
        )
    }
}