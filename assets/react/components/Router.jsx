
import { createBrowserRouter } from 'react-router-dom';
import HomePage from '../pages/Home';
import UserEditPage from '../pages/UserEdit';
import LayoutProtected from '../components/LayoutProtected';
import LayoutPublic from './LayoutPublic';
import SectionPage from '../pages/Section';
import ChapterPage from '../pages/Chapter';
import Layout from '../components/Layout';
import NotFoundPage from '../pages/NotFound';
import { dataLoader } from '../helpers/queryHelper';


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
        path: '*',
        element: <NotFoundPage />,
      },
    ]
  },
]);

