import React, {useEffect} from 'react';
import { Link } from "react-router";
import { useDataLoader } from '../hooks/useDataLoader';
import { useArticleAction } from '@/hooks/UseArticleAction';
import { SectionType } from '@/types/SectionType';
import { ChapterType } from '@/types/ChapterType';
import ButtonSmArticleAdd from '@/components/ButtonSmArticleAdd';

export default function Home(): React.JSX.Element|undefined {
    const data = useDataLoader('sections');
    const { setSectionOrigin } = useArticleAction();

    useEffect(() => {
        setSectionOrigin(undefined);
    }, [setSectionOrigin]);

    if (data) {
        return (
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5 pt-10">
                { data.member.map((section: SectionType) =>
                    <div key={section.id} className="max-w rounded overflow-hidden shadow-lg bg-gray-100 dark:bg-gray-800">
                        <div className="px-6 py-4">
                            <div className="font-bold text-xl text-blue-700 mb-2">
                                <Link to={`/section/${section.id}`}>{section.title}</Link> 
                            </div>
                            <ul>
                                { section.chapters.map((chapter:ChapterType) =>
                                    <li key={chapter.id}>
                                        <Link to={`/chapter/${chapter.id}`}>{chapter.title}</Link>
                                    </li>
                                )}
                            </ul>
                        </div>
                    </div>
                )}                
                <div className="fixed bottom-10 right-3 lg:hidden">
                    <ButtonSmArticleAdd />
                </div>
            </div>
        )
    }
}