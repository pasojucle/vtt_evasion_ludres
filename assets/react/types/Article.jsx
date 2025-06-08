import React from 'react';
import { useParams } from "react-router";


export default function Article({data}) {

    const {id} = useParams();

    return (
       <form className="space-y-4" action="#">
            <div className="grid gap-4 mb-4 grid-cols-2">
                <div className="col-span-2 sm:col-span-1">
                    <label htmlFor="article_charpter_section_title" className="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Rubrique</label>
                    <input type="text" name="article[charpter][section][title]" id="article_charpter_section_title" value={data.chapter.section.title} onChange={() => {}} className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
                </div>
                <div className="col-span-2 sm:col-span-1">
                    <label htmlFor="article_charpter_title" className="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Chapitre</label>
                    <input type="text" name="article[charpter][title]" id="article_charpter_title" value={data.chapter.title} onChange={() => {}} className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
                </div>
                <div className="col-span-2 sm:col-span-1">
                    <label htmlFor="article_title" className="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Titre</label>
                    <input type="text" name="article[title]" id="article_title" value={data.title} onChange={() => {}} className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
                </div>
                <div className="col-span-2">
                    <label htmlFor="article_content" className="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Contenu</label>
                    <textarea name="article[content]" id="article_content" value={data.content} onChange={() => {}} className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
                </div>
            </div>        
            <button type="submit" className="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Enregistrer</button>
        </form>
    )
}