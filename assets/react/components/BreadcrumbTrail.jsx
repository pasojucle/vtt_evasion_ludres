import React from 'react';
import { Link } from 'react-router-dom';

export default function BreadcrumbTrail({routes}) {

    const Item = ({breadcrumb}) => {
        console.log('item', breadcrumb);
        if (breadcrumb.path !== null) {
            return (
                <Link className= {`font-extrabold ${breadcrumb.color}`} to={breadcrumb.pathname}>{breadcrumb.title}</Link>
            )
        }
    }

    const Home = () => {
        return (
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
                    <path strokeLinecap="round" strokeLinejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
        )
    }

    const Separator = () => {
        return (
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
                <path strokeLinecap="round" strokeLinejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        )
    }

    const breadcrumbs = () => {
        const defaultColor = 'text-gray-800 dark text-gray-200';
        const breadcrumbs = [];

        console.log('routes', routes);
        routes.forEach((breadcrumb, index) => {
            console.log('routes index', index);
            if (index === 0) {
                breadcrumbs.push({'title': <Home />,'pathname': '/', 'color': defaultColor})
                breadcrumbs.push({'title': <Separator />,'pathname': null})
            }
            breadcrumb['color'] = (index === routes.length - 1) ? 'text-blue-700' : defaultColor;
            breadcrumbs.push(breadcrumb)
            if (index < routes.length - 1)  {
                breadcrumbs.push({'title': <Separator />,'pathname': null})
            }
        })
        console.log('breadcrumbs', breadcrumbs);

        return breadcrumbs;
    }

    return(
        <div className='flex mt-7 mb-4'>
            {breadcrumbs().map((breadcrumb, item) =>  
                <>
                    <Item key={item} breadcrumb={breadcrumb} />
                </>
            )}
        </div>
    )
}