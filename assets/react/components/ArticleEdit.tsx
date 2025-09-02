import { useState } from 'react';
import { useAuth } from '../hooks/useAuth';
import TiptapEditor from '@/form/TipTapEditor';
import AutocompleteType from '@/form/AutocompleteType';
import { Button } from '@/components/ui/button';
import { dataSender } from '@/helpers/queryHelper';
import { toast } from 'sonner';

import { ArticleType } from '@/types/ArticleType';
import { ChapterType } from '@/types/ChapterType';
import { SectionType } from '@/types/SectionType';

type ArticleEditProps = {
    article: ArticleType;
    parent?: ChapterType;
    sections: {member: SectionType[]};
    chapters: ChapterType[];
    handleChangeParent: (section: SectionType) => void;
    handleClose: () => void;
}

export default function ArticleEdit({article, parent, sections, chapters, handleChangeParent, handleClose }: ArticleEditProps): React.JSX.Element | undefined {

    const undefinedValue = {'@id': undefined, id: undefined}
    const { token } = useAuth();
    const [title, setTitle] = useState(article?.title);
    const [content, setContent] = useState(article?.content);
    const [section, setSection] = useState(parent ? parent.section : undefinedValue);
    const [chapter, setChapter] = useState(parent ?? undefinedValue);

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const data = {
            'section': undefined !== section['@id'] ? section['@id'] : section,
            'chapter': undefined !== chapter['@id'] ? chapter['@id'] : chapter,
            'title': title,
            'content': content,
        };
        console.log("handleSubmit", data)
        
        dataSender(
                undefined !== article.id ? 'PATCH' : 'POST',
                'articles', article?.id ?? undefined,
                token, JSON.stringify(data)
            ).then((response) => {

                
            console.log("toast", response)
            if (200 === response.status) {
                console.log("toast", `Modification ${title} réussi.`)
                toast(`Modification ${title} réussi.`)
                toast.success(`Modification ${title} réussi.`)
                
            }
            if (201 === response.status) {
                console.log("toast", `L'article ${title} a bien été ajouté.`)
                toast.success(`L'article ${title} a bien été ajouté.`)
            }
            handleClose();
        });
    }
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
                        <Button className='rounded-none rounded-s-lg' type="button" variant="secondary" onClick={handleClose}>Annuler</Button>
                        <Button className='rounded-none rounded-e-lg' type="submit">Enregistrer</Button>
                    </div>   
                </div>
            </form>
        </div>
    ) 
}