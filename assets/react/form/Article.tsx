import React, { useState, useEffect } from 'react';
import { useParams } from "react-router";
import TiptapEditor from '@/form/TipTapEditor';
import AutocompleteType from '@/form/AutocompleteType';
import { dataLoader, dataSender } from '@/helpers/queryHelper';
import { useAuth } from '../hooks/useAuth';


export default function Article({data}: any): React.JSX.Element|undefined {

    console.log('data', data)
    const {id} = useParams();
    const { token } = useAuth();
    const [sections, setSections] = useState([]);
    const [section, setSection] = useState((null !== data) ? data.chapter.section : {});

    const [chapters, setChapters] = useState([]);
    const [chapter, setChapter] = useState((null !== data) ? data.chapter : {});
    const [title, setTitle] = useState((null !== data) ? data.title : null);
    const [content, setContent] = useState((null !== data) ? data.content : null);

    useEffect(() => {
        dataLoader("sections")
        .then((result) => {
            setSections(result.data.member);
        })
    }, [])

    useEffect(() => {
        if (undefined !== section.id) {
            dataLoader(`chapters?section.id=${section.id}`)
            .then((result) => {
                setChapters(result.data.member);
            })
        }
    }, [section])

    const handleSelectSection = (value: any) => {
        console.log('handleSelectSection', value)
        setSection(value)
        setChapter({})
    }

    const handleRemoveSection = () => {
        console.log('handleRemoveSection')
        setSection({})
        setChapter({})
    }
    const handleSelectChapter = (value: any) => {
        console.log('handleSelectChapter', value)
        setChapter(value)
    }

    const handleRemoveChapter = () => {
        setChapter({})
    }

    const handleChangeTitle = (value: any) => {
        setTitle(value);
    }

    const handleChangeContent = (html: string) => {
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

    const handleSubmit = async () => {
        const data = {
            'section': undefined !== section['@id']? section['@id'] : section,
            'chapter': undefined !== chapter['@id']? chapter['@id'] : chapter,
            'title': title,
            'content': content,
        };
        console.log('handleSubmit', data)
        const result = dataSender('POST', 'articles', undefined, token, JSON.stringify(data));
    }

    console.log('article', data, section)
    if (data) {
        return (
            <form className="space-y-4" action="#" onSubmit={handleSubmit}>
                <div className="grid gap-4 mb-4 grid-cols-3">
                    <AutocompleteType list={sections} value={section} label='Rubrique' className='col-span-3 sm:col-span-1' placeholder='Sélectionnez une rubrique' handleSelect={handleSelectSection} handleRemove={handleRemoveSection} />
                    <AutocompleteType list={chapters} value={chapter} label='Chapitre' className='col-span-3 sm:col-span-1' placeholder='Sélectionnez un chapitre' handleSelect={handleSelectChapter} handleRemove={handleRemoveChapter} />
                    <div className="col-span-3 sm:col-span-1">
                        <label htmlFor="article_title" className="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">Titre</label>
                        <input type="text" name="article[title]" id="article_title" value={data.title} onChange={(e) => handleChangeTitle(e.target.value)} className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
                    </div>
                    <div className="col-span-3">
                        <TiptapEditor label='Contenu' content={data.content} handleChange={handleChangeContent}/>
                    </div>
                </div>        
                <button type="submit" className="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Enregistrer</button>
            </form>
        ) 
    }
}