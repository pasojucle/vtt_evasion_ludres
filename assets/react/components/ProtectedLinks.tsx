import React from "react";
import { useAuth } from "@/hooks/useAuth";
import { useModal} from '@/hooks/useModal';
import Article from '@/form/Article';
// import User from '@/form/User';

export default function Protecteds(): React.JSX.Element|undefined {

    const { token } = useAuth();
    const { show } = useModal();

    if (token) {
        return (
            <>
                <button onClick={() => show('Ajouter un article', 'article', 'lg')}
                    className="block mt-4 lg:inline-block lg:mt-0 hover:text-white px-4 py-2 rounded hover:bg-blue-700 mr-2">Ajouter un article
                </button>
                {/* <button onClick={() => show('Ajouter un utilisateur', User, 'md')}
                    className="block mt-4 lg:inline-block lg:mt-0 hover:text-white px-4 py-2 rounded hover:bg-blue-700 mr-2">Ajouter un utilisateur
                </button> */}
            </>
        )
    }
}