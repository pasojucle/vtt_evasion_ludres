import React, { useEffect, useState } from 'react';
import { useParams, useLocation } from "react-router";
import { useNavigate } from "react-router-dom";
import TiptapEditor from '@/form/TipTapEditor';
import { Button } from "@/components/ui/button"
import { CirclePlus } from 'lucide-react'; 
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from "@/components/ui/sheet"
import { dataSender } from '@/helpers/queryHelper';
import { toast } from 'sonner';
import { useAuth } from '../hooks/useAuth';
import { useArticleAction } from '@/hooks/UseArticleAction';
import { Combobox } from '@/components/ui/combobox';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';


export function ArticleSheet(): React.JSX.Element {

    const { 
        section,
        chapter,
        currentChapter,
        sections, 
        chapters,
        openArticleSheet,
        setOpenArticleSheet, 
        setEditArticle, 
        loadChapter,
        handleSelectChapter, 
        handleSelectSection, 
        handleAddSection,
        handleAddChapter
    } = useArticleAction();
    const undefinedValue = { '@id': undefined, id: undefined }
    const { id } = useParams();
    const location = useLocation();
    const { token, getToken } = useAuth();
    const navigate = useNavigate();

    const [title, setTitle] = useState("");
    const [content, setContent] = useState("");
    const [scrollDir, setScrollDir] = useState<"up" | "down" | null>(null);
    const [lastScrollY, setLastScrollY] = useState(0);

    // useEffect(() => {
    //     console.log("section", section)
    //     handleSelectSection("-1")
    // }, [sections])

    // useEffect(() => {
    //     console.log("chapter", chapter, chapters)
    //     handleSelectChapter("-1")
    // }, [chapters])

    useEffect(() => {
        const handleScroll = () => {
            const currentScrollY = window.scrollY;

            if (currentScrollY > lastScrollY) {
                setScrollDir("down");
            } else if (currentScrollY < lastScrollY) {
                setScrollDir("up");
            }

            setLastScrollY(currentScrollY);
        };

        window.addEventListener("scroll", handleScroll);

        return () => window.removeEventListener("scroll", handleScroll);
    }, [lastScrollY]);

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const data = {
            'section': undefined !== section?.['@id'] ? section['@id'] : {title: section?.title},
            'chapter': undefined !== chapter?.['@id'] ? chapter['@id'] : {title: chapter?.title},
            'title': title,
            'content': content,
        };
        console.log('handleSubmit', data)
        const token = await getToken();
        dataSender(
            'POST',
            'articles', undefined,
            token, JSON.stringify(data)
        ).then((response) => {
            if (201 === response.status) {
                toast.success(`L'article ${title} a bien été ajouté.`)
            }
            handleClose();
        });
    }


    const handleChangeTitle = (e:any) => {
        setTitle((e.target.value));
    }

    const handleChangeContent = (html: string) => {
        setContent(html);
    }

    const handleClose = () => {
        console.log('handleClose', location.pathname)
        setOpenArticleSheet(false);
        const path = `/chapter/${chapter?.id}`
        if (location.pathname !== path) {
            navigate(path);
            return;
        }
        loadChapter(chapter?.id)
    }

    const handleOpen = (isOpen: boolean) => {
        if (isOpen) {
            setEditArticle(null);
        }
        setOpenArticleSheet(isOpen);
    }

    return (
        <Sheet open={openArticleSheet} onOpenChange={(isOpen) => {handleOpen(isOpen)}}>
            <SheetTrigger asChild>
                <Button size="lg" tabIndex={0} className="lg:bg-transparent lg:text-foreground lg:hover:bg-transparent lg:hover:text-primary-lighten transition-[width] duration-500">
                    <CirclePlus /> <span className={scrollDir === "down" ? 'hidden' : 'block'}>Ajouter un article</span>
                </Button>
            </SheetTrigger>
            <SheetContent
                forceMount
                onOpenAutoFocus={(e) => e.preventDefault()}
                onPointerDownOutside={(e) => e.preventDefault()}
                onInteractOutside={(e) => e.preventDefault()}
                >
                <form onSubmit={handleSubmit}>
                    <SheetHeader>
                        <SheetTitle>Ajouter un article</SheetTitle>
                        <SheetDescription>
                            Ajouter un article ici. Cliquer sur "Enregistrer" quand vous avez terminé.
                        </SheetDescription>
                    </SheetHeader>
                    <div className="grid grid-cols-1 sm:grid-cols-3 flex-1 auto-rows-min gap-6 px-4">
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
                            <TiptapEditor label='Contenu' content={undefined} handleChange={handleChangeContent} />
                        </div>
                    </div>
                    <SheetFooter>
                        <div className='flex' role='group'>
                            <div className='ml-0 md:ml-auto inline-flex rounded-md shadow-xs' role='group'>
                                <SheetClose asChild>
                                    <Button className='w-1/2 md:w-auto rounded-none rounded-s-lg' type="button" variant="outline">Annuler</Button>
                                </SheetClose>
                                <Button className='w-1/2 md:w-auto rounded-none rounded-e-lg' type="submit">Enregistrer</Button>
                            </div>
                        </div>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    )
}
