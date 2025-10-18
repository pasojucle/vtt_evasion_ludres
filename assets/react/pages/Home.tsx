import React, { useEffect, useState } from 'react';
import { Link } from "react-router";
import { useAuth } from '../hooks/useAuth';
import { useDataLoader } from '../hooks/useDataLoader';
import { useArticleAction } from '@/hooks/UseArticleAction';
import { SectionType } from '@/types/SectionType';
import { ChapterType } from '@/types/ChapterType';
import { Pencil } from 'lucide-react';
import { dataLoader } from '@/helpers/queryHelper';
import SectionEdit from '@/components/SectionEdit';
import SectionDelete from '@/components/sectionDelete';
import {
    Card,
    CardAction,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import { ArticleSheet } from '@/components/ArticleSheet';
import CardSkeleton from '@/components/CardSkeleton';


export default function Home(): React.JSX.Element | undefined {
    const data = useDataLoader('sections');
    const { setSection, setChapter } = useArticleAction();
    const [sectionEdit, setSectionEdit] = useState<null | number>(null)
    const [sections, setSections] = useState<SectionType[] | null>(null)
    const { token, getToken } = useAuth();

    useEffect(() => {
        loadSections();
        setSection(undefined);
        setChapter(undefined)
    }, [])

    const loadSections = async () => {
        const accessToken = await getToken();
        dataLoader("sections", undefined, accessToken)
            .then((result) => {
                setSections(result.data.member);
            });
    }

    const closeSectionEdit = (refresh: boolean) => {
        setSectionEdit(null);
        if (refresh) {
            loadSections();
        }
    }

    const ButtonGroup = ({ section }: { section: SectionType | undefined }) => {
        if (undefined !== section && token) {
            return (
                <div className='ml-auto inline-flex items-start rounded-md shadow-xs' role='group'>
                    <button type="button" onClick={() => setSectionEdit(section.id)}
                        className="p-1 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                        <Pencil />
                    </button>
                    <SectionDelete section={section} loadSections={loadSections} />
                </div>
            )
        }
    }

    const SectionHeader = ({ section }: { section: SectionType }): React.JSX.Element => {
        if (sectionEdit === section.id) {
            return (
                <SectionEdit section={section} handleClose={closeSectionEdit} />
            )
        }
        return (
            <>
                <CardTitle>
                    <Link to={`/section/${section.id}`} className='font-bold text-xl text-blue-700 '>{section.title}</Link>
                </CardTitle>
                <CardAction>
                    <ButtonGroup section={section} />
                </CardAction>
            </>
        )
    }

    if (sections) {
        return (
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 pt-10">
                {sections.map((section: SectionType) =>
                    <Card key={section.id}>
                        <CardHeader>
                            <SectionHeader section={section} />
                        </CardHeader>
                        <CardContent>
                            <ul>
                                {section.chapters && section.chapters.map((chapter: ChapterType) =>
                                    <li key={chapter.id}>
                                        <Link className="hover:text-primary-lighten" to={`/chapter/${chapter.id}`}>{chapter.title}</Link>
                                    </li>
                                )}
                            </ul>
                        </CardContent>
                    </Card>
                )}                    
                <CardSkeleton nomberOfResults={sections?.length} />
                <div className="fixed z-30 bottom-10 right-3 lg:hidden">
                    <ArticleSheet />
                </div>
            </div>
        )
    }
}