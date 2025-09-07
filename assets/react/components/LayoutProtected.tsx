import React from 'react';
import { Outlet, Navigate } from "react-router-dom";
import { useAuth } from "../hooks/useAuth";


export default function LayoutProtected(): React.JSX.Element {

  const { token } = useAuth();

  if (!token) {

    return <Navigate to="/login" replace state={{ from: location }} />;
  }

  return (
      <>
        <Outlet />
      </>
    )
}