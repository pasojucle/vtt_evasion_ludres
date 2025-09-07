import React, { useEffect, useState} from 'react';
import { useParams, useNavigate } from "react-router-dom";
import { useDataLoader } from '@/hooks/useDataLoader';
import BreadcrumbTrail from '@/components/BreadcrumbTrail';
import { ArticleType } from '@/types/ArticleType';
import { SectionType } from '@/types/SectionType';
import { dataLoader } from '@/helpers/queryHelper';
import ArticleEdit from '@/components/ArticleEdit';


export default function SectionAddArticle(): React.JSX.Element {
    const { id } = useParams();
    const navigate = useNavigate();
    const data = useDataLoader('sections', id);
    const article: ArticleType = {
        title: '',
        content: '',
        section: undefined,
        chapter: undefined,
    }
    const [loaded, setLoaded] = useState(false);
    const sections = useDataLoader('sections') ?? {member: []};
    const [chapters, setChapters] = useState([]);
    const [newArticleObject, setNewArticleObject] = useState<ArticleType>(article)

    const handleClose = () => {
        navigate(`/section/${id}`)
    }

    const getChaptersBySection = (section: SectionType) => {
        if (undefined !== section.id) {
            dataLoader(`chapters?section.id=${section.id}`)
            .then((result) => {
                setChapters(result.data.member);
                article.section = section;
                setNewArticleObject(article);
            })
        }
    }

    useEffect(() => {
        dataLoader(`chapters?section.id=${id}`)
            .then((result) => {
                setChapters(result.data.member);
            })
        dataLoader(`sections/${id}`)
            .then((result) => {
                article.section = result.data;
                setNewArticleObject(article);
                setLoaded(true);
            });
    }, [])

    const routes = () => {
        return [
            {'title': data?.title,'pathname': `/section/${id}`}
        ];
    }
   
    const ArticleAdd = () => {
        if (loaded) {
            return (
                <ArticleEdit article={newArticleObject} parent={undefined} sections={sections} chapters={chapters} handleChangeParent={getChaptersBySection} handleClose={handleClose}/>
            )
        }
    }

    return (
        <div>
            <BreadcrumbTrail routes={routes()} />
            <div className='max-w-3xl mx-auto xl:max-w-none xl:mr-[17.5rem] xl:pr-16'>
                <ArticleAdd />
            </div>
        </div>
    )
}