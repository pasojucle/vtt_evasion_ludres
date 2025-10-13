import { useState } from 'react';
import { useAuth } from '../hooks/useAuth';
import TiptapEditor from '@/form/TipTapEditor';
import { Combobox } from '@/components/ui/combobox';
import { Button } from '@/components/ui/button';
import { dataSender } from '@/helpers/queryHelper';
import { toast } from 'sonner';
import { useArticleAction } from '@/hooks/UseArticleAction';
import { ArticleType } from '@/types/ArticleType';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';

type ArticleEditProps = {
    article: ArticleType;
    handleClose: () => void;
}

export default function ArticleEdit({article, handleClose }: ArticleEditProps): React.JSX.Element | undefined {
    const undefinedValue = {'@id': undefined, id: undefined}
    const { getToken } = useAuth();
    const [title, setTitle] = useState(article?.title);
    const [content, setContent] = useState(article?.content);    
    const { 
        section,
        chapter,
        sections, 
        chapters,
        handleSelectChapter, 
        handleSelectSection, 
        handleAddSection,
        handleAddChapter
    } = useArticleAction();
console.log("**** edit sections", sections);
    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const data = {
            'section': undefined !== section?.['@id'] ? section['@id'] : section,
            'chapter': undefined !== chapter?.['@id'] ? chapter['@id'] : chapter,
            'title': title,
            'content': content,
        };
        const token = await getToken();     
        dataSender(
                undefined !== article.id ? 'PATCH' : 'POST',
                'articles', article?.id ?? undefined,
                token, JSON.stringify(data)
            ).then((response) => {
            if (200 === response.status) {
                toast.success(`Modification ${title} réussi.`)
                
            }
            if (201 === response.status) {
                toast.success(`L'article ${title} a bien été ajouté.`)
            }
            handleClose();
        });
    }

    const handleChangeTitle = (e: any) => {
        setTitle((e.target.value));
    }

    const handleChangeContent = (html: string) => {
        setContent(html);
    }

    return (
        <div className="p-3 bg-background">
            <form className="space-y-4" action="#" onSubmit={handleSubmit}>
                <div className="grid gap-4 mb-4 grid-cols-3">
                    <Combobox 
                        label="Section"
                        items={sections} 
                        initialValue={section?.id} 
                        className='col-span-3 sm:col-span-1' 
                        placeholder='Sélectionnez une rubrique' 
                        handleSelect={handleSelectSection} 
                        handleAddItem={handleAddSection}/>
                    <Combobox 
                        label="Chapitre"
                        items={chapters} 
                        initialValue={chapter?.id} 
                        className='col-span-3 sm:col-span-1' 
                        placeholder='Sélectionnez un chapitre' 
                        handleSelect={handleSelectChapter} 
                        handleAddItem={handleAddChapter}/>
                    <div className="col-span-3 sm:col-span-1">
                        <Label>Titre</Label>
                        <Input type="text" value={title} onInput={handleChangeTitle}/>
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