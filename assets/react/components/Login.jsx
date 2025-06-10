import React from 'react';
import { useAuth } from "../hooks/useAuth";
import { useModal } from "../hooks/useModal";

export default function Login() {
    const { token , logout } = useAuth();
    const { show } = useModal();

    const handleLogin= () => {
        show('Authentification', 'login', 'sm')
      };

    const handleLogout = () => {
        logout();
      };

    const Render = () => {
        if (token) {
            return (
                <button className="block text-md px-4 py-2 rounded text-blue-700 font-bold hover:text-white mt-4 hover:bg-blue-700 lg:mt-0" onClick={handleLogout}>Logout</button>
            )
        }
        return (
            <button className="block text-md px-4 py-2 rounded text-blue-700 font-bold hover:text-white mt-4 hover:bg-blue-700 lg:mt-0" onClick={handleLogin}>Login</button>
        )
    }

    return (
        <Render/>
    )
}