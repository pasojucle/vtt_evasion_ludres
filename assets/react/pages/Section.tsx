import React, { useEffect, useState } from 'react';
import { useParams, Link } from "react-router";
import { useAuth } from '../hooks/useAuth';
import { Pencil } from 'lucide-react';
import BreadcrumbTrail from '../components/BreadcrumbTrail';
import { ChapterType } from '@/types/ChapterType';
import { ArticleType } from '@/types/ArticleType';
import { useArticleAction } from '@/hooks/UseArticleAction';
import { dataLoader } from '@/helpers/queryHelper';
import ChapterDelete from '@/components/ChapterDelete';
import ChapterEdit from '@/components/ChapterEdit';
import {
    Card,
    CardAction,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import { ArticleSheet } from '@/components/ArticleSheet';
import CardSkeleton from '@/components/CardSkeleton';


export default function Section(): React.JSX.Element | undefined {
    let { id } = useParams();
    const { section, setSection } = useArticleAction();
    const { token, getToken } = useAuth();
    const [chapterEdit, setChapterEdit] = useState<null | number>(null)

    const loadSection = async () => {
        const accessToken = await getToken();
        const result = await dataLoader(`sections/${id}`, undefined, accessToken);
        setSection(result.data);
    }
    useEffect(() => {
        loadSection();
    }, [])

    const routes = () => {
        if (null !== section) {
            return [
                { 'title': section?.title, 'pathname': `/section/${id}` }
            ];
        }
        return [];
    }

    const closeChapterEdit = (refresh: boolean) => {
        setChapterEdit(null);
        if (refresh) {
            loadSection();
        }
    }

    const ButtonGroup = ({ chapter }: { chapter: ChapterType | undefined }) => {
        if (undefined !== chapter && token) {
            return (
                <div className='ml-auto inline-flex items-start rounded-md shadow-xs' role='group'>
                    <button type="button" onClick={() => setChapterEdit(chapter.id)}
                        className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <Pencil />
                    </button>
                    <ChapterDelete chapter={chapter} loadSection={loadSection} />
                </div>
            )
        }
    }

    const ChapterHeader = ({ chapter }: { chapter: ChapterType }): React.JSX.Element => {
        if (chapterEdit === chapter.id) {
            return (
                <ChapterEdit chapter={chapter} handleClose={closeChapterEdit} />
            )
        }
        return (
            <>
                <CardTitle>
                    <Link to={`/chapter/${chapter.id}`} className='font-bold text-xl text-blue-700 '>{chapter.title}</Link>                            </CardTitle>
                <CardAction>
                    <ButtonGroup chapter={chapter} />
                </CardAction>
            </>
        )
    }

    if (section) {
        return (
            <div>
                <BreadcrumbTrail routes={routes()} />
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                    {section.chapters && section.chapters.map((chapter: ChapterType) => {
                        chapter.section = section;
                        return (
                            <Card key={section.id}>
                                <CardHeader>
                                    <ChapterHeader chapter={chapter} />
                                </CardHeader>
                                <CardContent>
                                    <ul>
                                        {chapter.articles && chapter.articles.map((article: ArticleType) =>
                                            <li key={article.id}>
                                                <Link className="hover:text-primary-lighten" to={`/chapter/${chapter.id}#${article.id}`}>{article.title}</Link>
                                            </li>
                                        )}
                                    </ul>
                                </CardContent>
                            </Card>
                        )
                    })}
                    <CardSkeleton nomberOfResults={section.chapters?.length} />
                </div>
                <div className="fixed bottom-10 right-3 lg:hidden">
                    <ArticleSheet />
                </div>
            </div>
        )
    }
}