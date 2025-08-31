import React, { useEffect, useState} from 'react';
import { useParams, useLocation, Link } from "react-router-dom";
import { useDataLoader } from '@/hooks/useDataLoader';
import { useScrollToLocation } from '@/hooks/UseScrollToLocation'
import BreadcrumbTrail from '@/components/BreadcrumbTrail';
import { ArticleType } from '@/types/ArticleType';
import Article from '@/components/Article';
import { SectionType } from '@/types/SectionType';
import { dataLoader } from '@/helpers/queryHelper';


export default function Chapter(): React.JSX.Element {
    const { id } = useParams();
    const chapter = useDataLoader('chapters', id);
    const sections = useDataLoader('sections');
    const location = useLocation();
    const hash = location.hash;
    useScrollToLocation(chapter, hash);
    const [chapters, setChapters] = useState([]);
    
    const getChaptersBySection = (section: SectionType) => {
        console.log('section ///', section)
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
                setChapters(result.data.member);
            })
    }, [])

    const routes = () => {
        return [
            {'title': chapter.section.title,'pathname': `/section/${chapter.section.id}`},
            {'title': chapter.title,'pathname': `/chapter/${id}`},
        ];
    }

    
    if (!chapter) {
        return (
            <div>Aucune donnée</div>
        )
    }

    if (0 < chapter.articles.length) {
        return (
            <div>
                <BreadcrumbTrail routes={routes()} />
                <div className='max-w-3xl mx-auto xl:max-w-none xl:mr-[17.5rem] xl:pr-16'>
                    <div className="relative z-20 prose prose-slate mt-8 dark:prose-dark flex flex-col gap-5">
                        { chapter.articles.map((article: ArticleType) =>
                            <Article key={article?.id} article={article} parent={chapter} sections={sections} chapters={chapters} handleChangeParent={getChaptersBySection}/>
                        )}
                    </div>
                </div>
                <div className="fixed z-20 top-[10rem] px-5 right-[max(0px,calc(50%-44rem))] w-[19.5rem] py-10 overflow-y-auto hidden xl:block shadow-lg bg-gray-100 dark:bg-gray-800">
                    <Link className='text-blue-700 font-bold mb-5 text-2xl' to={`/chapter/${chapter.id}#${chapter.articles[0].id}`}>Sommaire</Link>
                    <ul>
                        { chapter.articles.map((article: ArticleType) =>
                            <li key={article.id}>
                                <Link to={`/chapter/${chapter.id}#${article.id}`}> {article.title}</Link>
                            </li>
                        )}
                    </ul>
                </div>
            </div>
        )
    }

    return (
        <div>
            <BreadcrumbTrail routes={routes()} />
            <div>Aucun article</div>
        </div>
    )
}