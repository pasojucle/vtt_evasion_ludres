import React, { useState, useEffect } from 'react';
import { useLocation, Outlet, Link } from 'react-router-dom';
import { AuthProvider } from "@/hooks/useAuth";
import { ToastProvider } from '@/hooks/useToast';
import { ModalProvider } from '@/hooks/useModal';
import { useTheme } from '@/hooks/useTheme';
import { Toaster } from "@/components/ui/sonner";
import Modal from '@/components/Modal';
import ProtectedLinks  from "@/components/ProtectedLinks";
import Login from '@/components/Login';
import ThemePanel from '@/components/ThemePanel';

export default function Layout(): React.JSX.Element {
    const [collapseMenu, setCollapseMenu] = useState(true);
    const location = useLocation();
    useEffect(() => { setCollapseMenu(true) }, [location])

    const { projectName} = useTheme();
    console.log('test', projectName);
    const toggleMenu = () => {
        setCollapseMenu(!collapseMenu);
    }

    const classNameMenu = () => {
        let className = "menu w-full lg:block flex-grow lg:flex lg:items-center lg:w-auto lg:px-3 px-8";
        if (collapseMenu) {
            className += ' hidden'
        }

        return className;
    }

    const Search: React.FC  = () => {
        return (
            <>
                <input
                    className="border-2 border-gray-300 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 h-10 pl-2 pr-8 rounded-lg text-sm focus:outline-none"
                    type="search" name="search" placeholder="Search"/>
                <button type="submit" className="absolute right-0 top-0 mt-3 mr-2">
                    <svg className="text-gray-600 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg"
                        version="1.1" id="Capa_1" x="0px" y="0px"
                        viewBox="0 0 56.966 56.966"
                        width="512px" height="512px">
                        <path
                            d="M55.146,51.887L41.588,37.786c3.486-4.144,5.396-9.358,5.396-14.786c0-12.682-10.318-23-23-23s-23,10.318-23,23  s10.318,23,23,23c4.761,0,9.298-1.436,13.177-4.162l13.661,14.208c0.571,0.593,1.339,0.92,2.162,0.92  c0.779,0,1.518-0.297,2.079-0.837C56.255,54.982,56.293,53.08,55.146,51.887z M23.984,6c9.374,0,17,7.626,17,17s-7.626,17-17,17  s-17-7.626-17-17S14.61,6,23.984,6z"/>
                    </svg>
                </button>
            </>
        )
    }

    return (
        <AuthProvider>
            <ToastProvider>
                <ModalProvider>
                <div className="sticky z-40 top-0 shadow border-solid border-t-2 border-blue-700 bg-gray-100 dark:bg-gray-800">
                    <nav className="max-w-[90rem] mx-auto flex items-center justify-between flex-wrap py-4 lg:px-12">
                        <div className="flex items-center justify-between lg:w-auto w-full lg:border-b-0 pr-2 border-solid border-b-2 border-gray-300 pb-5 lg:pb-0">
                            <div className="flex items-center flex-shrink-0 text-gray-800 dark:text-gray-100">
                                <span className="font-semibold text-xl tracking-tight px-3 md:px-0">{projectName}</span>
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
                            <div className="text-md font-bold text-blue-700 lg:flex-grow">
                                <Link className="block mt-4 lg:inline-block lg:mt-0 hover:text-white px-4 py-2 rounded hover:bg-blue-700 mr-2" to="/">Index</Link>
                                    <ProtectedLinks />
                                </div>
                            <div className="relative mx-auto text-gray-600 lg:block hidden">
                                <Search/>
                            </div>
                            <div className="flex mx-2">
                                <ThemePanel />
                                <Login />
                            </div>
                        </div>
                    </nav>      
                </div>
                <div className="max-w-[90rem] mx-auto px-4 sm:px-6 md:px-8">
                    <Outlet />
                </div>
                <Modal />
                <Toaster />
                </ModalProvider>
            </ToastProvider>
        </AuthProvider>
    )
}