import React from 'react';

import { useParams , useLoaderData, Link } from "react-router";

export default function Section() {

    let {id} = useParams();

    const { data } = useLoaderData();
    console.log('data section', data)
    return (
        <>
            <div className="mx-auto">
                <h1 className="text-4xl font-extrabold my-4 text-blue-700">{data.title}</h1>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5">
                { data.chapters.map((chapter) =>
                    <div key={chapter.id} className="max-w rounded overflow-hidden shadow-lg bg-gray-200 dark:bg-gray-800">
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