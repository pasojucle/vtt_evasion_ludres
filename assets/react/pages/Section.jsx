import React from 'react';
import { useParams , Link } from "react-router";
import { useDataLoader } from '../hooks/useDataLoader';
import BreadcrumbTrail from '../components/BreadcrumbTrail';

export default function Section() {
    let {id} = useParams();
    const {data, error, httpResponse} = useDataLoader('sections', id);

    const routes = () => {
        return [
            {'title': data.title,'pathname': `/section/${data.id}`}
        ];
    }

    if (data.chapters) {
        return (
            <>
                <BreadcrumbTrail routes={routes()} />
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5">
                    { data.chapters.map((chapter) =>
                        <div key={chapter.id} className="max-w rounded overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800">
                            <div className="px-6 py-4">
                                <div className="font-bold text-xl mb-2">
                                    <Link to={`/chapter/${chapter.id}`}>{chapter.title}</Link>
                                </div>
                                <ul className="text-gray-700 text-base">
                                    { chapter.articles.map((article) =>
                                        <li key={article.id}>
                                            <Link to={`/chapter/${chapter.id}#${article.id}`}>{article.title}</Link>
                                        </li>
                                    )}
                                </ul>
                            </div>
                        </div>
                    )}
                </div>
            </>
        )
    }
}