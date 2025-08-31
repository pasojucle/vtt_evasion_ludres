import React, { useState } from 'react';
import { useNavigate } from "react-router";
import TiptapEditor from '@/form/TipTapEditor';
import AutocompleteType from '@/form/AutocompleteType';
import { dataSender } from '@/helpers/queryHelper';
import { ArticleType } from '@/types/ArticleType';
import TextRaw from '@/components/TextRaw';
import { toast } from 'sonner';
import { useAuth } from '../hooks/useAuth';
import { Pencil, CircleX } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { ChapterType } from '@/types/ChapterType';
import { SectionType } from '@/types/SectionType';

type ArticleProps = {
    article?: ArticleType;
    parent?: ChapterType;
    sections: {member: SectionType[]};
    chapters: ChapterType[];
    handleChangeParent: (section: SectionType) => void;
}

export default function Article({ article, parent, sections, chapters, handleChangeParent }: ArticleProps): React.JSX.Element | undefined {

    const { token } = useAuth();
    const navigate = useNavigate();
    const undefinedValue = {'@id': undefined, id: undefined}
    
    const [edit, setEdit] = useState(false);
    const [section, setSection] = useState(parent ? parent.section : undefinedValue);
    const [chapter, setChapter] = useState(parent ?? undefinedValue);
    const [title, setTitle] = useState(article?.title);
    const [content, setContent] = useState(article?.content);

    const handleSelectSection = (value: any) => {
        console.log('handleSelectSection', value)
        setSection(value)
        setChapter(undefinedValue)
        handleChangeParent(value);
    }

    const handleRemoveSection = () => {
        console.log('handleRemoveSection')
        setSection(undefinedValue)
        setChapter(undefinedValue)
    }
    const handleSelectChapter = (value: any) => {
        console.log('handleSelectChapter', value)
        setChapter(value)
    }

    const handleRemoveChapter = () => {
        setChapter(undefinedValue)
    }

    const handleChangeTitle = (e: any) => {
        setTitle((e.target.value));
    }

    const handleChangeContent = (html: string) => {
        console.log('content', html);
        setContent(html);
    }

    const handleEdit = () => {
        setChapter(parent ?? undefinedValue)
        setSection(parent?.section ?? undefinedValue)
        setEdit(true)
    }

    // if (!article) {
    //     article = {chapter: {
    //         title: null,
    //         content: null,
    //         section: {
    //             title: null,
    //         }
    //     }}
    // }

    const handleSubmit = async () => {
        const data = {
            'section': undefined !== section['@id'] ? section['@id'] : section,
            'chapter': undefined !== chapter['@id'] ? chapter['@id'] : chapter,
            'title': title,
            'content': content,
        };
        console.log("handleSubmit", data)
        dataSender('PATCH', 'articles', article?.id ?? undefined, token, JSON.stringify(data)).then((response) => {
            console.log("handleSubmit", response, response.status)
            if (200 === response.status) {
                console.log("toast", response.status)
                toast.success(`Modification ${title} réussi.`)
            }
        });
    }

    if (edit) {
        return (
            <div className="p-3 bg-background">
                <form className="space-y-4" action="#" onSubmit={handleSubmit}>
                    <div className="grid gap-4 mb-4 grid-cols-3">
                        <AutocompleteType list={sections.member} value={section} label='Rubrique' className='col-span-3 sm:col-span-1' placeholder='Sélectionnez une rubrique' handleSelect={handleSelectSection} handleRemove={handleRemoveSection} />
                        <AutocompleteType list={chapters} value={chapter} label='Chapitre' className='col-span-3 sm:col-span-1' placeholder='Sélectionnez un chapitre' handleSelect={handleSelectChapter} handleRemove={handleRemoveChapter} />
                        <div className="col-span-3 sm:col-span-1">
                            <label htmlFor="article_title" className="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">Titre</label>
                            <input type="text" name="article[title]" id="article_title" value={title} onInput={handleChangeTitle} className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
                        </div>
                        <div className="col-span-3">
                            <TiptapEditor label='Contenu' content={article?.content} handleChange={handleChangeContent}/>
                        </div>
                    </div>
                    <div className="flex">
                        <div className='ml-auto inline-flex rounded-md shadow-xs' role='group'>
                            <Button className='rounded-none rounded-s-lg' type="button" variant="secondary" onClick={() => setEdit(false)}>Annuler</Button>
                            <Button className='rounded-none rounded-e-lg' type="submit">Enregistrer</Button>
                        </div>   
                    </div>
                </form>
            </div>
        ) 
    }

    const deleteArticle = (article: ArticleType) => {
        dataSender('DELETE', 'articles', article.id, token).then((response) => {
            console.log('deleteArticle -----', response.status)
            if (204 === response.status) {
                navigate(location);
                toast.success(`Suppression ${article.title} réussi.`)
            }
        });
    }

    const ButtonGroup = ({article}:{article: ArticleType | undefined}) => {
            if (undefined !== article && token) {
                return (
                    <div className='ml-auto inline-flex rounded-md shadow-xs' role='group'>
                        <button type="button" onClick={handleEdit}
                            className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                            <Pencil />
                        </button>
                        <button type="button" onClick={() => {deleteArticle(article)}}
                            className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                            <CircleX />
                        </button>
                    </div>
                )
            }
        }

    return (
        <div id={String(article?.id)} className="max-w rounded overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800">
            <div className="px-6 py-4">
                <div className="flex flex-wrap items-center font-bold text-xl mb-2">
                    <div>{article?.title}</div>
                    <ButtonGroup article={article} />
                </div>
                <TextRaw textHtml={article?.content} />
            </div>
        </div>
    )
}