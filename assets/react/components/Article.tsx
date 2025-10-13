import React from 'react';
import TextRaw from '@/components/TextRaw';
import { useAuth } from '../hooks/useAuth';
import { Pencil, CircleX } from 'lucide-react';
import { ArticleType } from '@/types/ArticleType';
import ArticleEdit from './ArticleEdit';
import { useArticleAction } from '@/hooks/UseArticleAction';
import {
    Card,
    CardAction,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"

type ArticleProps = {
    article: ArticleType;
    handleDeleteArticle: () => void;
    refresh: () => void;
}

export default function Article({ article, handleDeleteArticle, refresh }: ArticleProps): React.JSX.Element | undefined {

    const { token } = useAuth();
    const { 
        editArticle,
        setEditArticle,
    } = useArticleAction();

    const handleEdit = () => {
        setEditArticle(article?.id ?? null)
    }

    const handleClose = () => {
        setEditArticle(null)
        refresh();
    }

    if (editArticle === article.id) {
        return (
            <ArticleEdit article={article} handleClose={handleClose} />
        )
    }

    const ButtonGroup = ({ article }: { article: ArticleType | undefined }) => {
        if (undefined !== article && token) {
            return (
                <div className='ml-auto inline-flex items-start rounded-md shadow-xs' role='group'>
                    <button type="button" onClick={handleEdit}
                        className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <Pencil />
                    </button>
                    <button type="button" onClick={handleDeleteArticle}
                        className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <CircleX />
                    </button>
                </div>
            )
        }
    }

    return (
        <Card id={String(article?.id)} >
            <CardHeader>
                <CardTitle>
                    {article?.title}
                </CardTitle>
                <CardAction>
                    <ButtonGroup article={article} />
                </CardAction>
            </CardHeader>
            <CardContent>
                <TextRaw textHtml={article?.content} />
            </CardContent>
        </Card>
    )
}