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
                <h1 className="text-4xl font-extrabold my-4 text-blue-700">{data.title}</h1>
            </div>
            <div className='max-w-3xl mx-auto xl:max-w-none xl:mr-[17.5rem] xl:pr-16'>
                <div className="relative z-20 prose prose-slate mt-8 dark:prose-dark">
                    { data.articles.map((article) =>
                        <div key={article.id} className="max-w rounded overflow-hidden shadow-lg bg-gray-200 dark:bg-gray-800 mb-5">
                            <div className="px-6 py-4">
                                <div className="font-bold text-xl mb-2">{article.title}</div>
                                <TextRaw textHtml={article.content} />
                            </div>
                        </div>
                    )}
                </div>
            </div>
            <div className="fixed z-20 top-[7.5rem] bottom-0 right-[max(0px,calc(50%-45rem))] w-[19.5rem] py-10 overflow-y-auto hidden xl:block">
                <h2 className='text-blue-700 font-bold'>Summary</h2>
            </div>
        </>
    )
}