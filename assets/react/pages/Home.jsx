import React from 'react';
import { useLoaderData, Link } from "react-router";

export default function Home() {
    const { data } = useLoaderData();
    return (
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
            { data.member.map((section) =>
                <div key={section.id} className="max-w rounded overflow-hidden shadow-lg">
                    <div className="px-6 py-4">
                        <div className="font-bold text-xl mb-2">
                            <Link to={`/section/${section.id}`}>{section.title}</Link> 
                        </div>
                        <ul className="text-gray-700 text-base">
                            { section.chapters.map((chapter) =>
                                <li key={chapter.id}>
                                    <Link to={`/chapter/${chapter.id}`}>{chapter.title}</Link>
                                </li>
                            )}
                        </ul>
                    </div>
                </div>
            )}
        </div>
    )
}