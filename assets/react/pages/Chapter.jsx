import React from 'react';
import { useParams, useLoaderData, Link } from "react-router";
import { useScrollToLocation } from '../hooks/UseScrollToLocation'
import BreadcrumbTrail from '../components/BreadcrumbTrail';
import TextRaw from '../components/TextRaw';

export default function Chapter() {
    useScrollToLocation();
    
    let {id} = useParams();

    const { data } = useLoaderData();

    const routes = () => {
        return [
            {'title': data.section.title,'pathname': `/section/${data.section.id}`},
            {'title': data.title,'pathname': `/chapter/${id}`},
        ];
    }
    console.log('data chapter', data)
    return (
        <>
            <BreadcrumbTrail routes={routes()} />
            <div className='max-w-3xl mx-auto xl:max-w-none xl:mr-[17.5rem] xl:pr-16'>
                <div className="relative z-20 prose prose-slate mt-8 dark:prose-dark">
                    { data.articles.map((article) =>
                        <div key={article.id} id={article.id} className="max-w rounded overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800 mb-5">
                            <div className="px-6 py-4">
                                <div className="font-bold text-xl mb-2">{article.title}</div>
                                <TextRaw textHtml={article.content} />
                            </div>
                        </div>
                    )}
                </div>
            </div>
            <div className="fixed z-20 top-[10rem] px-5 right-[max(0px,calc(50%-45rem))] w-[19.5rem] py-10 overflow-y-auto hidden xl:block shadow-lg bg-gray-100 dark:bg-gray-800">
                <h2 className='text-blue-700 font-bold mb-5 text-2xl'>Sommaire</h2>
                <ul>
                    { data.articles.map((article) =>
                        <li key={article.id}>
                            <Link to={`/chapter/${data.id}#${article.id}`}> {article.title}</Link>
                        </li>
                    )}
                </ul>
            </div>
        </>
    )
}