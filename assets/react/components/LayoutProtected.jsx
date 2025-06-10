import React from 'react';
import { Outlet, Navigate } from "react-router-dom";
import { useAuth } from "../hooks/useAuth";


export default function LayoutProtected() {

  const { token } = useAuth();

  if (!token) {
    // user is not authenticated
    console.log('redirect to login')
    return <Navigate to="/login" replace state={{ from: location }} />;
  }

  return (
      <>
        <h1>protected</h1>
  
        <Outlet />
      </>
    )
}