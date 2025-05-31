import React from 'react';

import { useParams, useLoaderData } from "react-router";
import TextRaw from '../components/TextRaw';

export default function Chapter() {

    
    let {id} = useParams();

    const { data } = useLoaderData();
    console.log('data chapter', data)
    return (
        <>
            <div className="mx-auto">
                <h1 className="text-5xl font-extrabold text-center my-5 dark:text-white">{data.title}</h1>
            </div>
            <div className="grid grid-cols-1 gap-3">
                { data.articles.map((article) =>
                    <div key={article.id} className="max-w rounded overflow-hidden shadow-lg">
                        <div className="px-6 py-4">
                            <div className="font-bold text-xl mb-2">{article.title}</div>
                            <p>
                                <TextRaw textHtml={article.content} />
                            </p>
                        </div>
                    </div>
                )}
            </div>
        </>
    )
}