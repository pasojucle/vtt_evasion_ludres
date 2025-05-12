import React from 'react';
import { Link } from "react-router-dom";
import { useAuth } from "../hooks/useAuth";

export default function Login() {
    const { logout } = useAuth();

    const handleLogout = () => {
        logout();
      };

    const Render = () => {
        const { user } = useAuth();
        if (user) {
            return (
                <button className="block text-md px-4 py-2 rounded text-blue-700 font-bold hover:text-white mt-4 hover:bg-blue-700 lg:mt-0" onClick={handleLogout}>Logout</button>
            )
        }
        return (
            <Link className="block text-md px-4 py-2 rounded text-blue-700 font-bold hover:text-white mt-4 hover:bg-blue-700 lg:mt-0" to="/login">Login</Link>
        )
    }

    return (
        <Render/>
    )
}