import React from 'react';

import { useParams , useLoaderData, Link } from "react-router";

export default function Section() {

    let {id} = useParams();

    const { data } = useLoaderData();
    console.log('data section', data)
    return (
        <>
            <div className="mx-auto">
                <h1 class="text-5xl font-extrabold text-center my-5 dark:text-white">{data.title}</h1>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                { data.chapters.map((chapter) =>
                    <div key={chapter.id} className="max-w rounded overflow-hidden shadow-lg">
                        <div className="px-6 py-4">
                            <div className="font-bold text-xl mb-2">
                                <Link to={`/chapter/${chapter.id}`}>{chapter.title}</Link>
                            </div>
                            <ul className="text-gray-700 text-base">
                                { chapter.articles.map((article) =>
                                    <li key={article.id}>
                                        <Link to={`/article/${article.id}`}>{article.title}</Link>
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