import React, { useState, useEffect } from 'react';
import { useLocation, Outlet, Link } from 'react-router-dom';
import { AuthProvider } from "@/hooks/useAuth";
import { useTheme } from '@/hooks/useTheme';
import { Toaster } from "@/components/ui/sonner";
import { Search as SearchIcon } from 'lucide-react';
import Login from '@/components/Login';
import ThemePanel from '@/components/ThemePanel';
import { ArticleAddProvider } from '@/hooks/UseArticleAdd';
import ButtonArticleAdd from '@/components/ButtonArticleAdd';


export default function Layout(): React.JSX.Element {
    const [collapseMenu, setCollapseMenu] = useState(true);
    const location = useLocation();
    useEffect(() => { setCollapseMenu(true) }, [location])

    const { projectName} = useTheme();
    const toggleMenu = () => {
        setCollapseMenu(!collapseMenu);
    }

    const classNameMenu = () => {
        let className = "menu w-full lg:block flex-grow lg:flex lg:items-center lg:w-auto mt-4 pt-4 border-t-1 border-gray-300 lg:border-none lg:mt-0 lg:pt-0";
        if (collapseMenu) {
            className += ' hidden'
        }

        return className;
    }

    const Search: React.FC  = () => {
        return (
            <div className="flex items-center border-2 border-gray-300 rounded-lg">
                <input
                    className="w-[calc(100%-24px)] dark:text-gray-200 bg-gray-100 dark:bg-gray-800 h-10 pl-2 pr-8 text-sm ring-0 focus:outline-none rounded-lg"
                    type="search" name="search" placeholder="Search"/>
                <button type="submit" className="ml-auto mr-2">
                    <SearchIcon />
                </button>
            </div>
        )
    }

    return (
        <AuthProvider>
            <ArticleAddProvider>
            <div className="sticky z-40 top-0 shadow border-solid border-t-2 border-blue-700 bg-gray-100 dark:bg-gray-800">
                <nav className="max-w-[90rem] mx-auto flex items-center justify-between flex-wrap py-4 lg:px-4">
                    <div className="flex items-center justify-between lg:w-auto w-full px-4">
                        <div className="flex items-center flex-shrink-0 text-gray-800 dark:text-gray-100">
                            <span className="font-semibold text-xl tracking-tight md:px-0">{projectName}</span>
                        </div>
                        <div className="relative mx-auto text-gray-600 block lg:hidden">
                            <Search/>
                        </div>
                        <div className="block lg:hidden" onClick={() => toggleMenu()}>
                            <button
                                id="nav"
                                className="flex items-center px-3 py-2 border-2 rounded text-blue-700 border-blue-700 hover:text-blue-700 hover:border-blue-700">
                                <svg className="fill-current h-3 w-3" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><title>Menu</title>
                                    <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div className={classNameMenu()}>
                        <div className="hidden lg:block">
                            <ButtonArticleAdd />
                        </div>
                        <div className="relative w-1/3 2xl:w-1/2 mx-auto text-gray-600 lg:block hidden">
                            <Search/>
                        </div>
                        <div className="flex w-full lg:max-w-max flex-col lg:flex-row pl-4 lg:pl-0 lg:gap-8">
                            <ThemePanel />
                            <Login />
                        </div>
                    </div>
                </nav>      
            </div>
            <div className="max-w-[90rem] mx-auto lg:px-4 sm:px-6 md:px-8 pb-8">
                <Outlet />
            </div>
            <Toaster />
            </ArticleAddProvider>
        </AuthProvider>
    )
}