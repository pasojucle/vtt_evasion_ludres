import React, {useEffect, useState} from 'react';
import { Link } from "react-router";
import { useAuth } from '../hooks/useAuth';
import { useDataLoader } from '../hooks/useDataLoader';
import { useArticleAction } from '@/hooks/UseArticleAction';
import { SectionType } from '@/types/SectionType';
import { ChapterType } from '@/types/ChapterType';
import ButtonSmArticleAdd from '@/components/ButtonSmArticleAdd';
import { Pencil } from 'lucide-react';
import { dataLoader } from '@/helpers/queryHelper';
import SectionEdit from '@/components/SectionEdit';
import SectionDelete from '@/components/sectionDelete';


export default function Home(): React.JSX.Element|undefined {
    const data = useDataLoader('sections');
    const { setSectionOrigin } = useArticleAction();
    const [sectionEdit, setSectionEdit] = useState<null | number>(null)
    const [sections, setSections] = useState<SectionType[] | null>(null)
    const { token } = useAuth();
    
    useEffect(() => {
        loadSections();
    }, [])

    useEffect(() => {
        setSectionOrigin(undefined);
    }, [setSectionOrigin]);

    const loadSections = async () => {
        dataLoader("sections")
            .then((result) => {
                setSections(result.data.member);
            });
    }

    const closeSectionEdit = (refresh: boolean) => {
        setSectionEdit(null);
        if (refresh) {
            console.log("refresh")
            loadSections();
        }
    }

    const ButtonGroup = ({section}:{section: SectionType | undefined}) => {
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
                    <Link to={`/section/${section.id}`} className='font-bold text-xl text-blue-700 '>{section.title}</Link>
                    <ButtonGroup section={section} />
                </>
            )
        }

    if (sections) {
        return (
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 pt-10">
                { sections.map((section: SectionType) =>
                    <div key={section.id} className="max-w rounded overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800">
                        <div className="flex border border-b-2 px-6 py-4">
                            <SectionHeader section={section} />
                        </div>
                        <ul className="px-6 py-4">
                            { section.chapters.map((chapter:ChapterType) =>
                                <li key={chapter.id}>
                                    <Link to={`/chapter/${chapter.id}`}>{chapter.title}</Link>
                                </li>
                            )}
                        </ul>
                    </div>
                )}                
                <div className="fixed bottom-10 right-3 lg:hidden">
                    <ButtonSmArticleAdd />
                </div>
            </div>
        )
    }
}