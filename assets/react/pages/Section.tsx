import React, {useEffect} from 'react';
import { useParams , Link} from "react-router";
import { useDataLoader } from '../hooks/useDataLoader';
import BreadcrumbTrail from '../components/BreadcrumbTrail';
import { ChapterType } from '@/types/ChapterType';
import { ArticleType } from '@/types/ArticleType';
import { useArticleAdd } from '@/hooks/UseArticleAdd';
import ButtonSmArticleAdd from '@/components/ButtonSmArticleAdd';

export default function Section(): React.JSX.Element|undefined {
    let {id} = useParams();
    const { setSectionOrigin } = useArticleAdd();

    const data = useDataLoader('sections', id);
    useEffect(() => {
        if (data) {
            setSectionOrigin(data);
        }
    }, [data, setSectionOrigin]);

    const routes = () => {
        return [
            { 'title': data.title, 'pathname': `/section/${id}` }
        ];
    }

    if (data) {
        return (
            <div>
                <BreadcrumbTrail routes={routes()} />
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5">
                    { data.chapters.map((chapter: ChapterType) =>
                        <div key={chapter.id} className="max-w rounded overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800 px-6 py-4">
                            <div className="font-bold text-xl text-blue-700 mb-2">
                                <Link to={`/chapter/${chapter.id}`}>{chapter.title}</Link>
                            </div>
                            <ul>
                                { chapter.articles.map((article: ArticleType) =>
                                    <li key={article.id}>
                                        <Link to={`/chapter/${chapter.id}#${article.id}`}>{article.title}</Link>
                                    </li>
                                )}
                            </ul>
                        </div>
                    )}
                </div>
                <div className="fixed bottom-10 right-3 lg:hidden">
                    <ButtonSmArticleAdd />
                </div>
            </div>
        )
    }
}