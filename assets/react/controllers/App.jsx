import React from 'react';
import { createBrowserRouter, RouterProvider, Navigate } from 'react-router-dom';
import { ProtectedRoute } from "../components/ProtectedRoute";
import HomePage from '../pages/Home';
import SectionEdit from '../pages/SectionEdit';
import LayoutSection from '../components/LayoutSection';
import Section from '../pages/Section';
import Layout from '../components/Layout';
import LoginPage from '../pages/Login';


const router = createBrowserRouter([
  {
    path: '/',
    element: <Layout />,
    children: [
      {
        index: true,
        element: <HomePage />,
      },
      {
        path: 'section',
        element: <LayoutSection />,
        children: [
          {
            path: ':id',
            element: <Section />
          },
          {
            path: ':id/edit',
            element: (
              <ProtectedRoute>
                <SectionEdit />
              </ProtectedRoute>
            )
          },
        ]
      },

      {
        path: 'login',
        element: <LoginPage />,
      },
    ]
  },
]);

export default function App() {
  return <RouterProvider router={router} />;
}