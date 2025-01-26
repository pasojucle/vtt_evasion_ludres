import React from 'react';

export default function TextRaw({textHtml, className=''}) {

    const createMarkup = () => {
        return {__html: textHtml};
    }

    return (
        <div className={className} dangerouslySetInnerHTML={createMarkup()} />
    )
}