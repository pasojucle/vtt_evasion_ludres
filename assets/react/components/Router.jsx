
import { createBrowserRouter,  } from 'react-router-dom';
import HomePage from '../pages/Home';
import ArticleEditPage from '../pages/ArticleEdit';
import UserEditPage from '../pages/UserEdit';
import LayoutProtected from '../components/LayoutProtected';
import LayoutSection from '../components/LayoutSection';
import SectionPage from '../pages/Section';
import Layout from '../components/Layout';
import LoginPage from '../pages/Login';
import NotFoundPage from '../pages/NotFound'

export const router = createBrowserRouter([
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
              element: <SectionPage />
            },
          ]
        },
        {
          path: '/article',
          element: <LayoutProtected />,
          children: [
            {
              path: 'add',
              element: <ArticleEditPage />
            },
            {
              path: ':id/edit',
              element: <ArticleEditPage />
            },
          ]
        },
  
        {
          path: '/user',
          element: <LayoutProtected />,
          children: [
            {
              path: 'add',
              element: <UserEditPage />
            },
            {
              path: ':id/edit',
              element: <UserEditPage />
            },
          ]
        },
        {
          path: 'login',
          element: <LoginPage />,
        },
        {
          path: '*',
          element: <NotFoundPage />,
        },
      ]
    },
  ]);