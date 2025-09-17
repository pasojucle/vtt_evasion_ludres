import React from 'react';
import { Outlet, Navigate } from "react-router-dom";
import { useAuth } from "../hooks/useAuth";
import { useLocation } from "react-router-dom";

export default function LayoutProtected(): React.JSX.Element {

  const { token } = useAuth();
  const location = useLocation();

  if (!token) {

    return <Navigate to="/" replace state={{ from: location }} />;
  }

  return (
      <>
        <Outlet />
      </>
    )
}