import { useState } from 'react';
import { useAuth } from '../hooks/useAuth';
import { dataSender } from '@/helpers/queryHelper';
import { toast } from 'sonner';

import { ChapterType } from '@/types/ChapterType';
import TitleEdit from '@/components/TitleEdit';

type ArticleEditProps = {
    chapter: ChapterType;
    handleClose: (refresh:boolean) => void;
}

export default function ChapterEdit({chapter, handleClose }: ArticleEditProps): React.JSX.Element | undefined {
    const { getToken } = useAuth();
    const [title, setTitle] = useState(chapter.title);

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const data = {
            'title': title,
        };
        const token = await getToken();   
        dataSender(
                'PATCH',
                'chapters', chapter.id,
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
        <TitleEdit title={title} handleChangeTitle={handleChangeTitle} handleSubmit={handleSubmit} handleClose={handleClose} />
    ) 
}