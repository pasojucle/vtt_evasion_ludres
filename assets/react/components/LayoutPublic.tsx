import React from 'react';
import { Outlet } from "react-router-dom";


export default function LayoutPublic(): React.JSX.Element {

    return (
        <>
          <Outlet />
        </>
      )
}