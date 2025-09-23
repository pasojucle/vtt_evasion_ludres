import React, { useState } from 'react';
import { useAuth } from "@/hooks/useAuth";
import { Button } from './ui/button';

export default function Loginl(): React.JSX.Element {
    const [open, setOpen] = useState<boolean>(false);
    const [message, setMessage] = useState<string | null>(null);
    const [email, setEmail] = useState<string>('');
    const [password, setPassword] = useState<string>('');
    const { token, login, logout } = useAuth();
    
    const overlayClassName = () => {
        const visibility = (open) ? 'visible bg-gray-500/70' : 'invisible bg-gray-100/0 dark:bg-gray-800/0';

        return `block fixed w-full h-full top-0 left-0 z-90 ${visibility} transition duration-1000 ease-in-out`;
    }

    const dialogClassName = () => {
        const position = (open) ? 'top-0 md:top-[5vh]' : '-top-[100vh]';
        return `fixed block z-100 w-full md:w-20/100 inset-x-0 md:inset-x-40/100 bg-gray-100 dark:bg-gray-800 ${position} transition-all duration-1000 ease-in-out] max-h-full md:max-h-90/100 overflow-y-auto`;
    }

    const Message = () => {
      if (message) {
        return (
          <p className="text-red-600">{message}</p>
        )
      }
      return null;
    };

    const handleLogin = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: {
            'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username: email, password: password }),
        });
        if (response.ok) {
            const jsonResult = await response.json();
            console.log('login', jsonResult);
            setOpen(false);
            login(jsonResult)

            return;
        }
        if (401 === response.status) {
            setMessage('Identifiant ou mot de passe incorrecte');
            return;
        }
        setMessage(response.statusText);
    };

    return (
        <>
            {token
                ? <Button className="px-0 justify-start" variant="ghost" type="button" onClick={logout}>Logout</Button>
                : <Button className="px-0 justify-start" variant="ghost" type="button" onClick={() => setOpen(true)}>Login</Button>
            }
                    
            <div className={overlayClassName()} tabIndex={-1} role="dialog">           
                <div className={ dialogClassName() } role="document">
                    <div className="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                        <h3 className="!mt-0 !mb-0 text-xl font-semibold text-gray-900 dark:text-white">Authentification</h3>
                        <button type="button" onClick={() => setOpen(false)} className="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="authentication-modal">
                            <svg className="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                            <span className="sr-only">Fermer</span>
                        </button>
                    </div>
                    <form onSubmit={handleLogin}>
                        <div className="p-4 md:p-5 space-y-4">
                            <div>
                                <label htmlFor="email" className="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Identifiant</label>
                                <input type="email" name="email" id="email" value={email} onChange={(event) => { setEmail(event.target.value) }} autoComplete="username"
                                className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
                            </div>
                            <div>
                                <label htmlFor="password" className="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Mot de passe</label>
                                <input type="password" name="password" id="password" value={password} onChange={(event) => { setPassword(event.target.value) }} autoComplete="current-password"
                                className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
                            </div>
                            <Message />
                        </div>
                        <div className="p-4 md:p-5 grid grid-cols-1 md:grid-cols-2 gap-3 border-t dark:border-gray-600 border-gray-200 mt-3">
                            <Button variant="secondary" onClick={() => setOpen(false)}>
                            Annuler
                            </Button>
                            <Button type="submit">S'authentifier</Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    )
}