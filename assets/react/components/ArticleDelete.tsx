import React, { useState } from 'react';
import { Button } from './ui/button';
import { useArticleAction } from '@/hooks/UseArticleAction';

type ArticleDeleteProps = {
    handleConfirm: () => void;
}

export default function ArticleDelete({handleConfirm}: ArticleDeleteProps): React.JSX.Element {
    const { deleteArticle, setDeleteArticle } = useArticleAction();

    const open = null !== deleteArticle;
    
    const overlayClassName = () => {
        const visibility = (open) ? 'visible bg-gray-500/70' : 'invisible bg-gray-100/0 dark:bg-gray-800/0';

        return `block fixed w-full h-full top-0 left-0 z-90 ${visibility} transition duration-1000 ease-in-out`;
    }

    const dialogClassName = () => {
        const position = (open) ? 'top-0 md:top-[5vh]' : '-top-[100vh]';
        return `fixed block z-100 w-full md:w-20/100 inset-x-0 md:inset-x-40/100 bg-gray-100 dark:bg-gray-800 ${position} transition-all duration-1000 ease-in-out] max-h-full md:max-h-90/100 overflow-y-auto`;
    }

    return (
        <div className={overlayClassName()} tabIndex={-1} role="dialog">           
            <div className={ dialogClassName() } role="document">
                <div className="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 className="!mt-0 !mb-0 text-xl font-semibold text-gray-900 dark:text-white">Suppression d'un article</h3>
                    <button type="button" onClick={() => setDeleteArticle(null)} className="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="authentication-modal">
                        <svg className="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span className="sr-only">Fermer</span>
                    </button>
                </div>
                <div className="p-4 md:p-5">
                    <p>La suppression d'un article est définitive</p>
                    <p>{`Est-vous sûr de voiloir supprimer l'article ${deleteArticle?.title} ?`}</p>
                    
                </div>
                <div className="p-4 md:p-5 grid grid-cols-1 md:grid-cols-2 gap-3 border-t dark:border-gray-600 border-gray-200 mt-3">
                    <Button variant="secondary" onClick={() => setDeleteArticle(null)}>
                        Annuler
                    </Button>
                    <Button type="button" variant="destructive" onClick={handleConfirm} >Confimer</Button>
                </div>
            </div>
        </div>
    )
}