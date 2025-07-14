import React, { createElement } from 'react';
import { useModal } from '../hooks/useModal';
import Article from '../types/Article';
import Login from '../types/Login';
import Parameters from '../types/Parameters';


export default function Modal() {

    const { shown, title, component, size, data, hide } = useModal();
    
    const sizes = {
        sm: 'w-full md:w-20/100 inset-x-0 md:inset-x-40/100',
        md: 'w-full md:w-40/100 inset-x-0 md:inset-x-30/100',
        lg: 'w-full md:w-60/100 inset-x-0 md:inset-x-20/100'
    };

    const overlayClassName = () => {
        const visibility = (shown) ? 'visible bg-gray-500/70' : 'invisible bg-gray-100/0 dark:bg-gray-800/0';

        return `block fixed w-full h-full top-0 left-0 z-90 ${visibility} transition duration-1000 ease-in-out`;
    }

    const dialogClassName = () => {
        const position = (shown) ? 'top-0 md:top-[5vh]' : '-top-[100vh]';
        return `fixed block z-100 ${sizes[size]} bg-gray-100 dark:bg-gray-800 ${position} transition-all duration-1000 ease-in-out] max-h-full md:max-h-90/100 overflow-y-auto`;
    }

    const Components = {
        login: Login,
        article: Article,
        parameters: Parameters
    };

    const ModalContent = () => {
        if (component) {
            return React.createElement(Components[component], {
                data: data,
            });
        }
    }

    return (<div className={overlayClassName()} tabIndex="-1" role="dialog">           
        <div className={ dialogClassName() } role="document">
            <div className="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 className="!mt-0 !mb-0 text-xl font-semibold text-gray-900 dark:text-white">
                    {title}
                </h3>
                <button type="button" onClick={hide} className="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="authentication-modal">
                    <svg className="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span className="sr-only">Fermer</span>
                </button>
            </div>
            <div className="p-4 md:p-5">
                <ModalContent />
            </div>
        </div>
    </div>)
}