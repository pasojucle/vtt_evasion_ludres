import React, { useEffect, useState } from 'react';
import { useParams, useLocation, Link } from "react-router-dom";
import { useDataLoader } from '@/hooks/useDataLoader';
import { useScrollToLocation } from '@/hooks/UseScrollToLocation'
import BreadcrumbTrail from '@/components/BreadcrumbTrail';
import { ArticleType } from '@/types/ArticleType';
import { ChapterType } from '@/types/ChapterType';
import Article from '@/components/Article';
import { SectionType } from '@/types/SectionType';
import { dataLoader, dataSender } from '@/helpers/queryHelper';
import { Button } from '@/components/ui/button';
import { CirclePlus } from 'lucide-react';
import { useArticleAction } from '@/hooks/UseArticleAction';
import ArticleDelete from '@/components/ArticleDelete'
import { toast } from 'sonner';
import { useAuth } from '../hooks/useAuth';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import { ArticleSheet } from '@/components/ArticleSheet';


export default function Chapter(): React.JSX.Element {
    const { id } = useParams();
    const {
        currentChapter,
        loadChapter,
        setOpenArticleSheet
    } = useArticleAction();
    const [deleteArticle, setDeleteArticle] = useState<ArticleType | null>(null);
    const location = useLocation();
    const hash = location.hash;
    useScrollToLocation(currentChapter, hash);

    const { getToken } = useAuth();

    const routes = () => {
        if (null !== currentChapter) {
            return [
                { 'title': currentChapter?.section?.title, 'pathname': `/section/${currentChapter?.section?.id}` },
                { 'title': currentChapter?.title, 'pathname': `/chapter/${id}` },
            ];
        }

        return [];
    }

    useEffect(() => {
        loadChapter(id);
    }, []);

    if (!currentChapter) {
        return (
            <div>Aucune donnée</div>
        )
    }

    const confirmDeleteArticle = async () => {
        const accessToken = await getToken();
        dataSender('DELETE', 'articles', deleteArticle?.id, accessToken).then((response) => {
            if (204 === response.status) {
                toast.success(`Suppression ${deleteArticle?.title} réussi.`);
                loadChapter(id);
            }
        });
        setDeleteArticle(null)
    }

    if (currentChapter?.articles && 0 < currentChapter.articles.length) {
        return (
            <div>
                <BreadcrumbTrail routes={routes()} />
                <div className='max-w-3xl mx-auto xl:max-w-none xl:mr-[17.5rem] xl:pr-16'>
                    <div className="relative z-0 prose prose-slate dark:prose-dark flex flex-col gap-5 mt-8">
                        {currentChapter.articles.map((article: ArticleType) => {
                            article.chapter = currentChapter;
                            article.section = currentChapter.section;
                            return (
                                <Article
                                    key={article?.id}
                                    article={article}
                                    handleDeleteArticle={() => setDeleteArticle(article)}
                                    refresh={() => loadChapter(id)} />
                            )
                        }
                        )}
                    </div>
                </div>
                <Card className="fixed z-20 top-[10rem] right-[max(16px,calc(50%-44rem))] w-[19.5rem] overflow-y-auto hidden xl:flex">
                    <CardHeader>
                        <CardTitle>
                            <Link className='text-blue-700 font-bold mb-5 text-2xl' to={`/chapter/${currentChapter.id}#${currentChapter.articles[0].id}`}>Sommaire</Link>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul>
                            {currentChapter.articles && currentChapter.articles.map((article: ArticleType) =>
                                <li key={article.id}>
                                    <Link className="hover:text-primary-lighten" to={`/chapter/${currentChapter.id}#${article.id}`}> {article.title}</Link>
                                </li>
                            )}
                        </ul>
                    </CardContent>
                </Card>
                <div className="fixed z-30 bottom-10 right-3 lg:hidden">
                    <ArticleSheet />
                </div>
                <ArticleDelete handleConfirm={confirmDeleteArticle} handleCancel={() => setDeleteArticle(null)} article={deleteArticle} />
            </div>
        )
    }

    return (
        <div>
            <BreadcrumbTrail routes={routes()} />
            <div className='max-w-3xl mx-auto xl:max-w-none xl:mr-[17.5rem] xl:pr-16'>
                <div className="relative z-20 prose prose-slate dark:prose-dark flex flex-wrap gap-5 mt-8">
                    <div className="w-full lg:w-4/12 overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800 flex items-center">
                        <Button variant="ghost" size="lg" className="w-full flex-col h-48" onClick={() => setOpenArticleSheet(true)}><CirclePlus className="size-10" /> Ajouter un article</Button>
                    </div>
                </div>
            </div>
        </div>
    )
}