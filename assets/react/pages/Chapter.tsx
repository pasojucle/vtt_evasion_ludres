import React, { useEffect, useState} from 'react';
import { useParams, useLocation, Link } from "react-router-dom";
import { useDataLoader } from '@/hooks/useDataLoader';
import { useScrollToLocation } from '@/hooks/UseScrollToLocation'
import BreadcrumbTrail from '@/components/BreadcrumbTrail';
import { ArticleType } from '@/types/ArticleType';
import { ChapterType } from '@/types/ChapterType';
import Article from '@/components/Article';
import { SectionType } from '@/types/SectionType';
import { dataLoader } from '@/helpers/queryHelper';
import { Button } from '@/components/ui/button';
import { CirclePlus, Plus } from 'lucide-react';
import ArticleEdit from '@/components/ArticleEdit';
import { useArticleAction } from '@/hooks/UseArticleAction';
import ButtonSmArticleAdd from '@/components/ButtonSmArticleAdd';
import ArticleDelete from '@/components/ArticleDelete'
import { toast } from 'sonner';
import { dataSender } from '@/helpers/queryHelper';
import { useAuth } from '../hooks/useAuth';


export default function Chapter(): React.JSX.Element {
    const { id } = useParams();
    const { setSectionOrigin, setChapterOrigin, addArticle, setAddArticle, deleteArticle, setDeleteArticle} = useArticleAction();
    const [chapter, setChapter] = useState<ChapterType | null>(null);
    const sections = useDataLoader('sections') ?? {member: []};
    const location = useLocation();
    const hash = location.hash;
    useScrollToLocation(chapter, hash);
    const [chapters, setChapters] = useState([]);
    const { token } = useAuth();

    const loadChapter = async() => {
        dataLoader(`chapters/${id}`)
            .then((result) => {
                setChapter(result.data);
            });
    }
    const handleClose = () => {
        setAddArticle(false);
        loadChapter();
    }

    const getChaptersBySection = (section: SectionType) => {
        if (undefined !== section.id) {
            dataLoader(`chapters?section.id=${section.id}`)
            .then((result) => {
                setChapters(result.data.member);
            })
        }
    }

    useEffect(() => {
        dataLoader(`chapters?section.id=${id}`)
            .then((result) => {
                const chapterOrigin = result.data.member;
                setChapters(chapterOrigin);
                setChapterOrigin(chapterOrigin)
                setSectionOrigin(chapterOrigin.section)
            })
        loadChapter();
    }, [])

    const routes = () => {
        if (null !== chapter) {
            return [
                {'title': chapter.section.title,'pathname': `/section/${chapter.section.id}`},
                {'title': chapter.title,'pathname': `/chapter/${id}`},
            ]; 
        }

        return [];
    }
    
    if (!chapter) {
        return (
            <div>Aucune donnée</div>
        )
    }

    const confirmDeleteArticle = () => {
        dataSender('DELETE', 'articles', deleteArticle?.id, token).then((response) => {
            if (204 === response.status) {
                toast.success(`Suppression ${deleteArticle?.title} réussi.`);
                loadChapter();
            }
        });
        setDeleteArticle(null)
    }


    const NewArticle = (): React.JSX.Element | undefined => {
        if (addArticle) {
            const newArticleObject =  {
                title: '',
                content: '',
                section: chapter?.section,
                chapter: chapter
            }
            return (
                <ArticleEdit article={newArticleObject} parent={chapter} sections={sections} chapters={chapters} handleChangeParent={getChaptersBySection} handleClose={handleClose}/>
            )
        }
    }

    if (0 < chapter.articles.length) {
        return (
            <div>
                <BreadcrumbTrail routes={routes()} />
                <div className='max-w-3xl mx-auto xl:max-w-none xl:mr-[17.5rem] xl:pr-16'>
                    <div className="relative z-0 prose prose-slate dark:prose-dark flex flex-col gap-5 mt-8">
                        { chapter.articles.map((article: ArticleType) =>
                            <Article key={article?.id} article={article} parent={chapter} sections={sections} chapters={chapters} handleChangeParent={getChaptersBySection} refresh={loadChapter}/>
                        )}
                        <NewArticle />
                    </div>
                </div>
                <div className="fixed z-20 top-[10rem] p-5 right-[max(16px,calc(50%-44rem))] w-[19.5rem] overflow-y-auto hidden xl:block shadow-lg bg-gray-100 dark:bg-gray-800">
                    <Link className='text-blue-700 font-bold mb-5 text-2xl' to={`/chapter/${chapter.id}#${chapter.articles[0].id}`}>Sommaire</Link>
                    <ul>
                        { chapter.articles.map((article: ArticleType) =>
                            <li key={article.id}>
                                <Link to={`/chapter/${chapter.id}#${article.id}`}> {article.title}</Link>
                            </li>
                        )}
                    </ul>
                </div>
                <div className="fixed z-30 bottom-10 right-3 lg:hidden">
                    <ButtonSmArticleAdd />
                </div>
                <ArticleDelete handleConfirm={confirmDeleteArticle} />
            </div>
        )
    }

    return (
        <div>
            <BreadcrumbTrail routes={routes()} />
            <div className='max-w-3xl mx-auto xl:max-w-none xl:mr-[17.5rem] xl:pr-16'>
                <div className="relative z-20 prose prose-slate dark:prose-dark flex flex-wrap gap-5 mt-8">
                    {addArticle 
                        ? <NewArticle />
                        : <div className="w-full lg:w-4/12 overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800 flex items-center">
                            <Button variant="ghost" size="lg" className="w-full flex-col h-48" onClick={() => setAddArticle(true)}><CirclePlus className="size-10" /> Ajouter un article</Button>
                        </div>
                    }
                </div>
            </div>
        </div>
    )
}