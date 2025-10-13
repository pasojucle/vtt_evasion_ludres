import { useState } from 'react';
import { useAuth } from '../hooks/useAuth';
import { dataSender } from '@/helpers/queryHelper';
import { toast } from 'sonner';

import { SectionType } from '@/types/SectionType';
import TitleEdit from '@/components/TitleEdit';

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

    const handleChangeTitle = (e: React.ChangeEvent<HTMLInputElement>) => {
        setTitle((e.currentTarget.value));
    }

    return (
        <TitleEdit title={title} handleChangeTitle={handleChangeTitle} handleSubmit={handleSubmit} handleClose={handleClose} />
    ) 
}