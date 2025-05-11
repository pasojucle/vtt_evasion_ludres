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
                <button onClick={handleLogout}>Logout</button>
            )
        }
        return (
            <Link to="/login">Login</Link>
        )
    }

    return (
        <Render/>
    )
}