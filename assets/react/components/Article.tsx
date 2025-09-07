import React, { useState } from 'react';
import { dataSender } from '@/helpers/queryHelper';
import TextRaw from '@/components/TextRaw';
import { useAuth } from '../hooks/useAuth';
import { Pencil, CircleX } from 'lucide-react';
import { toast } from 'sonner';
import { ArticleType } from '@/types/ArticleType';
import { ChapterType } from '@/types/ChapterType';
import { SectionType } from '@/types/SectionType';
import ArticleEdit from './ArticleEdit';

type ArticleProps = {
    article: ArticleType;
    parent?: ChapterType;
    sections: {member: SectionType[]};
    chapters: ChapterType[];
    handleChangeParent: (section: SectionType) => void;
    refresh: () => void;
}

export default function Article({ article, parent, sections, chapters, handleChangeParent, refresh }: ArticleProps): React.JSX.Element | undefined {

    const { token } = useAuth();
    
    const [edit, setEdit] = useState(false);

    const handleEdit = () => {
        setEdit(true)
    }

    const handleClose = () => {
        setEdit(false)
        refresh();
    }

    if (edit) {
        return (
            <ArticleEdit article={article} parent={parent} sections={sections} chapters={chapters} handleChangeParent={handleChangeParent} handleClose={handleClose} />
        ) 
    }

    const deleteArticle = (article: ArticleType) => {

        dataSender('DELETE', 'articles', article.id, token).then((response) => {
            if (204 === response.status) {
                toast.success(`Suppression ${article.title} réussi.`);
                refresh();
            }
        });
    }

    const ButtonGroup = ({article}:{article: ArticleType | undefined}) => {
        if (undefined !== article && token) {
            return (
                <div className='ml-auto inline-flex rounded-md shadow-xs' role='group'>
                    <button type="button" onClick={handleEdit}
                        className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <Pencil />
                    </button>
                    <button type="button" onClick={() => {deleteArticle(article)}}
                        className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <CircleX />
                    </button>
                </div>
            )
        }
    }

    return (
        <div id={String(article?.id)} className="max-w rounded overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800">
            <div className="px-6 py-4">
                <div className="flex flex-wrap items-center font-bold text-xl mb-2">
                    <div>{article?.title}</div>
                    <ButtonGroup article={article} />
                </div>
                <TextRaw textHtml={article?.content} />
            </div>
        </div>
    )
}