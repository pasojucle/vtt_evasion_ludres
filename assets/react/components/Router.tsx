
import { createBrowserRouter, RouteObject } from 'react-router-dom';
import HomePage from '@/pages/Home';
// import UserEditPage from '@/pages/UserEdit';
import LayoutProtected from '@/components/LayoutProtected';
import LayoutPublic from '@/components/LayoutPublic';
import SectionPage from '@/pages/Section';
import ChapterPage from '@/pages/Chapter';
import Layout from '@/components/Layout';
import NotFoundPage from '@/pages/NotFound';
import ErrorPage from '@/pages/Error';


const routeConfig: RouteObject[] = [
{
    path: '/',
    Component: Layout,
    children: [
      {
        index: true,
        Component: HomePage,
      },
      {
        path: 'section',
        Component: LayoutPublic,
        children: [
          {
            path: ':id',
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
            Component: ChapterPage,
          },
        ]
      },
      // {
      //   path: '/user',
      //   element: <LayoutProtected />,
      //   children: [
      //     {
      //       path: 'add',
      //       element: <UserEditPage />
      //     },
      //     {
      //       path: ':id/edit',
      //       element: <UserEditPage />
      //     },
      //   ]
      // },
      {
        path: '*',
        element: <NotFoundPage />,
      },
    ]
  },
];


export const router = createBrowserRouter(routeConfig);

