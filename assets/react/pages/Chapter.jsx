import React from 'react';
import { useParams, useLocation, Link } from "react-router";
import { useAuth } from "../hooks/useAuth";
import { useModal } from "../hooks/useModal";
import { useDataLoader } from '../hooks/useDataLoader';
import { useScrollToLocation } from '../hooks/UseScrollToLocation'
import BreadcrumbTrail from '../components/BreadcrumbTrail';
import TextRaw from '../components/TextRaw';

export default function Chapter() {
    const { token } = useAuth();
    const { id } = useParams();
    const { data, error, httpResponse } = useDataLoader('chapters', id);
    const { hash } = useLocation();
    useScrollToLocation(data, hash);
    const { show } = useModal();

    const routes = () => {
        return [
            {'title': data.section.title,'pathname': `/section/${data.section.id}`},
            {'title': data.title,'pathname': `/chapter/${id}`},
        ];
    }

    const ButtonGroup = ({article}) => {
        if (token) {
            return (
                <div className='ml-auto inline-flex rounded-md shadow-xs' role='group'>
                    <button type="button" onClick={() => {show('Modifier', 'article', 'md', 'articles', article.id)}}
                        className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </button>
                    <button type="button" className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </button>
                </div>
            )
        }
    }
    console.log('chapter data', data)
    if (data) {
        return (
            <>
                <BreadcrumbTrail routes={routes()} />
                <div className='max-w-3xl mx-auto xl:max-w-none xl:mr-[17.5rem] xl:pr-16'>
                    <div className="relative z-20 prose prose-slate mt-8 dark:prose-dark">
                        { data.articles.map((article) =>
                            <div key={article.id} id={article.id} className="max-w rounded overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800 mb-5">
                                <div className="px-6 py-4">
                                    <div className="flex flex-wrap items-center font-bold text-xl mb-2">
                                        <div>{article.title}</div>
                                        <ButtonGroup article={article} />
                                    </div>
                                    <TextRaw textHtml={article.content} />
                                </div>
                            </div>
                        )}
                    </div>
                </div>
                <div className="fixed z-20 top-[10rem] px-5 right-[max(0px,calc(50%-45rem))] w-[19.5rem] py-10 overflow-y-auto hidden xl:block shadow-lg bg-gray-100 dark:bg-gray-800">
                    <Link className='text-blue-700 font-bold mb-5 text-2xl' to={`/chapter/${data.id}#${data.articles[0].id}`}>Sommaire</Link>
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
}