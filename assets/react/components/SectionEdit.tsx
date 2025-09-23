import { useState } from 'react';
import { useAuth } from '../hooks/useAuth';
import { dataSender } from '@/helpers/queryHelper';
import { toast } from 'sonner';

import { SectionType } from '@/types/SectionType';
import { Save, CircleX } from 'lucide-react';

type SectionEditProps = {
    section: SectionType;
    handleClose: (refresh:boolean) => void;
}

export default function SectionEdit({section, handleClose }: SectionEditProps): React.JSX.Element | undefined {
    const { getToken } = useAuth();
    const [title, setTitle] = useState(section.title);

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const data = {
            'title': title,
        };
        const token = await getToken();
        dataSender(
                'PATCH',
                'sections', section.id,
                token, JSON.stringify(data)
            ).then((response) => {
            if (200 === response.status) {
                toast.success(`Modification ${title} réussi.`)
            }
            handleClose(true);
        });
    }

    const handleChangeTitle = (e: any) => {
        setTitle((e.target.value));
    }

    return (
        <form className="flex w-full" onSubmit={handleSubmit}>
           <input type="text" name="chapter[title]" id="section_title" value={title} onInput={handleChangeTitle} className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
            <div className='ml-auto inline-flex rounded-md shadow-xs' role='group'>
                <button 
                    type="button" 
                    onClick={() => handleClose(false)}
                    className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                    <CircleX />
                </button>
                <button type="submit" className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                    <Save />
                </button>
            </div>
        </form>
    ) 
}