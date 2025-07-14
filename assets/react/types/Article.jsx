import React, { useState } from 'react';
import { useParams } from "react-router";
import TiptapEditor from './TipTapEditor';


export default function Article({data}) {

    const {id} = useParams();
    const [content, setContent] = useState(null);

    const handleChangeContent = (html) => {
        console.log('content', html);
        setContent(html);
    }


    console.log('article', data)
    if (!data) {
        data = {chapter: {
            title: null,
            content: null,
            section: {
                title: null,
            }
        }}
    }

    const toolbar = [ 'bold', 'italic', 'underline', '|', 'fontColor', '|', 'alignment', '|', 'heading']

    console.log('article', data)
    if (data) {
        return (
            <form className="space-y-4" action="#">
                <div className="grid gap-4 mb-4 grid-cols-3">
                    <div className="col-span-3 sm:col-span-1">
                        <label htmlFor="article_charpter_section_title" className="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">Rubrique</label>
                        <input type="text" name="article[charpter][section][title]" id="article_charpter_section_title" value={data.chapter.section.title} onChange={() => {}} className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
                    </div>
                    <div className="col-span-3 sm:col-span-1">
                        <label htmlFor="article_charpter_title" className="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">Chapitre</label>
                        <input type="text" name="article[charpter][title]" id="article_charpter_title" value={data.chapter.title} onChange={() => {}} className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
                    </div>
                    <div className="col-span-3 sm:col-span-1">
                        <label htmlFor="article_title" className="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">Titre</label>
                        <input type="text" name="article[title]" id="article_title" value={data.title} onChange={() => {}} className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
                    </div>
                    <div className="col-span-3">
                        <TiptapEditor id='article_content' label='Contenu' name='article[content]' content={data.content} handleChange={handleChangeContent} upload_url='' toolbar={toolbar} environment='dprodev'/>
                    </div>
                </div>        
                <button type="submit" className="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Enregistrer</button>
            </form>
        ) 
    }

}