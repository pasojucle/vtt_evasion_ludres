import React, { useEffect, useState} from 'react';
import { useParams, useNavigate } from "react-router-dom";
import { useDataLoader } from '@/hooks/useDataLoader';
import BreadcrumbTrail from '@/components/BreadcrumbTrail';
import { ArticleType } from '@/types/ArticleType';
import { SectionType } from '@/types/SectionType';
import { dataLoader } from '@/helpers/queryHelper';
import ArticleEdit from '@/components/ArticleEdit';
import { useArticleAction } from '@/hooks/UseArticleAction';


export default function ArticleAction(): React.JSX.Element {
    const { sectionOrigin, chapterOrigin} = useArticleAction();
    const navigate = useNavigate();
    const article: ArticleType = {
        title: '',
        content: '',
        section: sectionOrigin,
        chapter: chapterOrigin,
    }
    const sections = useDataLoader('sections') ?? {member: []};
    const [chapters, setChapters] = useState([]);
    const [newArticleObject, setNewArticleObject] = useState<ArticleType>(article)

    const handleClose = () => {
        if (sectionOrigin) {
           navigate(`/section/${sectionOrigin.id}`);
        } else {
            navigate("/");
        }
    }

    const getChaptersBySection = (section: SectionType | undefined) => {
        if (section && undefined !== section.id) {
            dataLoader(`chapters?section.id=${section.id}`)
            .then((result) => {
                setChapters(result.data.member);
                article.section = section;
                setNewArticleObject(article);
            })
        }
    }

    useEffect(() => {
        getChaptersBySection(sectionOrigin);
    }, [])

    const routes = () => {
        if (sectionOrigin) {
            return [
                {'title': sectionOrigin.title,'pathname': `/section/${sectionOrigin.id}`}
            ];
        }
        return [];
    }
   
    return (
        <div>
            <BreadcrumbTrail routes={routes()} />
            <div className='max-w-3xl mx-auto xl:max-w-none xl:mr-[17.5rem] xl:pr-16'>
                <ArticleEdit article={newArticleObject} sections={sections} chapters={chapters} handleChangeParent={getChaptersBySection} handleClose={handleClose}/>
            </div>
        </div>
    )
}