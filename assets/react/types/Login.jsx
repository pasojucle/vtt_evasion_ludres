import React, { useState } from 'react';
import { useAuth } from "../hooks/useAuth";
import { useModal } from '../hooks/useModal';
import { dataSender } from '../helpers/queryHelper';

export default function Login() {

    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const { login } = useAuth();
    const { hide } = useModal();

    const handleLogin = async (e) => {
      e.preventDefault();
      const formData = new FormData();
      formData.append('login[email]', email);
      formData.append('login[password]', password);
      dataSender('login', formData).then(async (jsonResponse) => {
        console.log('response', jsonResponse);
        if (jsonResponse.user) {
          await login({ email });
          hide();
        } else {
          
          alert("Invalid username or password");
        }

      })
    };

    return (
       <form className="space-y-4" action="#" onSubmit={handleLogin}>
              <div>
                  <label htmlFor="email" className="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Adresse mail</label>
                  <input type="email" name="email" id="email" value={email} onChange={(event) => {setEmail(event.target.value)}} className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
              </div>
              <div>
                  <label htmlFor="password" className="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Mot de passe</label>
                  <input type="password" name="password" id="password" value={password} onChange={(event) => {setPassword(event.target.value)}} className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
              </div>
            <button type="submit" className="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">S'authentifier</button>
        </form>
    )
}