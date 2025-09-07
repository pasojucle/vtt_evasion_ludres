import React from 'react';
import { useParams , Link} from "react-router";
import { useDataLoader } from '../hooks/useDataLoader';
import BreadcrumbTrail from '../components/BreadcrumbTrail';
import { ChapterType } from '@/types/ChapterType';
import { ArticleType } from '@/types/ArticleType';
import { Button } from '@/components/ui/button';
import { CirclePlus } from 'lucide-react';

export default function Section(): React.JSX.Element|undefined {
    let {id} = useParams();
    const data = useDataLoader('sections', id);

    const routes = () => {
        return [
            {'title': data.title,'pathname': `/section/${id}`}
        ];
    }

    if (data) {
        return (
            <div>
                <BreadcrumbTrail routes={routes()} />
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5">
                    { data.chapters.map((chapter: ChapterType) =>
                        <div key={chapter.id} className="max-w rounded overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800 px-6 py-4">
                            <div className="font-bold text-xl mb-2">
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
                    <div className="max-w rounded overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800 px-6 py-4">
                        <Link to={`/section/${id}/add/article`}>
                            <Button variant="ghost" size="lg" className="w-full h-full flex-col"><CirclePlus className="size-10"/> Ajouter un article</Button>
                        </Link>
                    </div>
                </div>
            </div>
        )
    }
}