
import { createBrowserRouter } from 'react-router-dom';
import HomePage from '../pages/Home';
import ArticleEditPage from '../pages/ArticleEdit';
import UserEditPage from '../pages/UserEdit';
import LayoutProtected from '../components/LayoutProtected';
import LayoutPublic from './LayoutPublic';
import SectionPage from '../pages/Section';
import ChapterPage from '../pages/Chapter';
import Layout from '../components/Layout';
import LoginPage from '../pages/Login';
import NotFoundPage from '../pages/NotFound'


export const dataLoader = async (param) => {
  const res = await fetch(`/api/${param}`);
  const jsonResult = await res.json();

  return jsonResult;
};

export const router = createBrowserRouter([
  {
    path: '/',
    Component: Layout,
    children: [
      {
        index: true,
        loader: async () => {
          return { data: await dataLoader('sections') };
        },
        Component: HomePage,
      },
      {
        path: 'section',
        Component: LayoutPublic,
        children: [
          {
            path: ':id',
            loader: async ({ params }) => {
              return { data: await dataLoader(`sections/${params.id}`) };
            },
            Component: SectionPage,
          },
        ]
      },
      {
        path: '/chapter',
        Component: LayoutPublic,
        children: [
          {
            path: ':id',
            loader: async ({ params }) => {
              return { data: await dataLoader(`chapters/${params.id}`) };
            },
            Component: ChapterPage,
          },
        ]
      },
      {
        path: 'article',
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

