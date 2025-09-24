import React, { useState } from 'react';
import { Button } from './ui/button';
import { CircleX } from 'lucide-react';
import { toast } from 'sonner';
import { dataSender } from '@/helpers/queryHelper';
import { useAuth } from '../hooks/useAuth';
import { SectionType } from '@/types/SectionType';

type ArticleDeleteProps = {
    section: SectionType;
    loadSections: () => void;
}

export default function SectionDelete({section, loadSections}: ArticleDeleteProps): React.JSX.Element {
    const [open, setOpen] = useState<boolean>(false);
        const { getToken } = useAuth();
    
    const overlayClassName = () => {
        const visibility = (open) ? 'visible bg-gray-500/70' : 'invisible bg-gray-100/0 dark:bg-gray-800/0';

        return `block fixed w-full h-full top-0 left-0 z-90 ${visibility} transition duration-1000 ease-in-out`;
    }

    const dialogClassName = () => {
        const position = (open) ? 'top-0 md:top-[5vh]' : '-top-[100vh]';
        return `fixed block z-100 w-full md:w-20/100 inset-x-0 md:inset-x-40/100 bg-gray-100 dark:bg-gray-800 ${position} transition-all duration-1000 ease-in-out] max-h-full md:max-h-90/100 overflow-y-auto`;
    }

    const confirmDeleteSection = async() => {
        const token = await getToken();
        dataSender('DELETE', 'sections', section?.id, token).then((response) => {
            if (204 === response.status) {
                toast.success(`Suppression ${section?.title} réussi.`);
                loadSections();
            }
        });
        setOpen(false)
    }


    return (
        <>
            <button type="button" onClick={() => {setOpen(true)}}
                className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                <CircleX />
            </button>
            <div className={overlayClassName()} tabIndex={-1} role="dialog">           
                <div className={ dialogClassName() } role="document">
                    <div className="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                        <h3 className="!mt-0 !mb-0 text-xl font-semibold text-gray-900 dark:text-white">Suppression d'un article</h3>
                        <button type="button" onClick={() => setOpen(false)} className="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="authentication-modal">
                            <svg className="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                            <span className="sr-only">Fermer</span>
                        </button>
                    </div>
                    <div className="p-4 md:p-5">
                        <p>Attention : la suppression d'une section supprimera toutes les  chapitres et tous les arcles liés. Cette suppression est définitive.</p>
                        <p>{`Est-vous sûr de voiloir supprimer le chapitre ${section?.title} ?`}</p>
                        
                    </div>
                    <div className="p-4 md:p-5 grid grid-cols-1 md:grid-cols-2 gap-3 border-t dark:border-gray-600 border-gray-200 mt-3">
                        <Button variant="secondary" onClick={() => setOpen(false)}>
                            Annuler
                        </Button>
                        <Button type="button" variant="destructive" onClick={confirmDeleteSection} >Confimer</Button>
                    </div>
                </div>
            </div>
        </>
    )
}