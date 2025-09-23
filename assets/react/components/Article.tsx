import React, { useState } from 'react';
import TextRaw from '@/components/TextRaw';
import { useAuth } from '../hooks/useAuth';
import { Pencil, CircleX } from 'lucide-react';
import { ArticleType } from '@/types/ArticleType';
import { ChapterType } from '@/types/ChapterType';
import { SectionType } from '@/types/SectionType';
import ArticleEdit from './ArticleEdit';
import { useArticleAction } from '@/hooks/UseArticleAction';


type ArticleProps = {
    article: ArticleType;
    parent?: ChapterType;
    sections: {member: SectionType[]};
    chapters: ChapterType[];
    handleChangeParent: (section: SectionType) => void;
    refresh: () => void;
}

export default function Article({ article, sections, chapters, handleChangeParent, refresh }: ArticleProps): React.JSX.Element | undefined {

    const { token } = useAuth();
    const { deleteArticle, setDeleteArticle } = useArticleAction();
    
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
            <ArticleEdit article={article} sections={sections} chapters={chapters} handleChangeParent={handleChangeParent} handleClose={handleClose} />
        ) 
    }

    const handleDeleteArticle = (article: ArticleType) => {
        setDeleteArticle(article);
    }

    const ButtonGroup = ({article}:{article: ArticleType | undefined}) => {
        if (undefined !== article && token) {
            return (
                <div className='ml-auto inline-flex items-start rounded-md shadow-xs' role='group'>
                    <button type="button" onClick={handleEdit}
                        className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <Pencil />
                    </button>
                    <button type="button" onClick={() => {handleDeleteArticle(article)}}
                        className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <CircleX />
                    </button>
                </div>
            )
        }
    }

    return (
        <div id={String(article?.id)} className="max-w rounded overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800">
            <div className="px-6 py-4 flex flex-wrap items-center font-bold text-xl border border-b-2">
                <div className="text-blue-700 max-w-[calc(100%-80px)]">{article?.title}</div>
                <ButtonGroup article={article} />
            </div>
            <TextRaw className='px-6 py-4' textHtml={article?.content} />
        </div>
    )
}