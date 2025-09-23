import React, { useEffect, useState } from 'react';
import { useParams, Link } from "react-router";
import { useAuth } from '../hooks/useAuth';
import { Pencil } from 'lucide-react';
import BreadcrumbTrail from '../components/BreadcrumbTrail';
import { ChapterType } from '@/types/ChapterType';
import { ArticleType } from '@/types/ArticleType';
import { useArticleAction } from '@/hooks/UseArticleAction';
import ButtonSmArticleAdd from '@/components/ButtonSmArticleAdd';
import { dataLoader } from '@/helpers/queryHelper';
import { SectionType } from '@/types/SectionType';
import ChapterDelete from '@/components/ChapterDelete';
import ChapterEdit from '@/components/ChapterEdit';


export default function Section(): React.JSX.Element | undefined {
    let { id } = useParams();
    const { setSectionOrigin } = useArticleAction();
    const { token } = useAuth();
    const [chapterEdit, setChapterEdit] = useState<null | number>(null)
    const [section, setSection] = useState<SectionType | null>(null)

    const loadSection = async () => {
        dataLoader(`sections/${id}`)
            .then((result) => {
                setSection(result.data);
            });
    }
    useEffect(() => {
        loadSection();
    }, [])

    useEffect(() => {
        if (section) {
            setSectionOrigin(section);
        }
    }, [section, setSectionOrigin]);

    const routes = () => {
        if (null !== section) {
            return [
                { 'title': section?.title, 'pathname': `/section/${id}` }
            ];
        }
        return [];
    }

    const closeChapterEdit = (refresh: boolean) => {
        setChapterEdit(null);
        if (refresh) {
            loadSection();
        }
    }

    const ButtonGroup = ({ chapter }: { chapter: ChapterType | undefined }) => {
        if (undefined !== chapter && token) {
            return (
                <div className='ml-auto inline-flex items-start rounded-md shadow-xs' role='group'>
                    <button type="button" onClick={() => setChapterEdit(chapter.id)}
                        className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <Pencil />
                    </button>
                    <ChapterDelete chapter={chapter} loadSection={loadSection} />
                </div>
            )
        }
    }

    const ChapterHeader = ({ chapter }: { chapter: ChapterType }): React.JSX.Element => {
        if (chapterEdit === chapter.id) {
            return (
                <ChapterEdit chapter={chapter} handleClose={closeChapterEdit} />
            )
        }
        return (
            <>
                <Link to={`/chapter/${chapter.id}`} className='font-bold text-xl text-blue-700 '>{chapter.title}</Link>
                <ButtonGroup chapter={chapter} />
            </>
        )
    }

    if (section) {
        return (
            <div>
                <BreadcrumbTrail routes={routes()} />
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                    {section.chapters.map((chapter: ChapterType) => {
                        chapter.section = section;
                        return (
                            <div key={chapter.id} className="max-w rounded overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800">
                                <div className="flex border border-b-2 px-6 py-4">
                                    <ChapterHeader chapter={chapter} />
                                </div>
                                <ul className='px-6 py-4'>
                                    {chapter.articles.map((article: ArticleType) =>
                                        <li key={article.id}>
                                            <Link to={`/chapter/${chapter.id}#${article.id}`}>{article.title}</Link>
                                        </li>
                                    )}
                                </ul>
                            </div>
                        )
                    })}
                </div>
                <div className="fixed bottom-10 right-3 lg:hidden">
                    <ButtonSmArticleAdd />
                </div>
            </div>
        )
    }
}