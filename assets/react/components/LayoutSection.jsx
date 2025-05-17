import React from 'react';
import { Outlet, Link } from "react-router-dom";
import { AuthProvider } from "../hooks/useAuth";


export default function Layout() {

    return (
        <>
          <h1>Section</h1>
    
          <Outlet />
        </>
      )
}