import React from 'react';

export default function TextRaw({textHtml, className=''}: {textHtml: string, className?: string}): React.JSX.Element {

    const createMarkup = () => {
        return {__html: textHtml};
    }

    return (
        <div className={className} dangerouslySetInnerHTML={createMarkup()} />
    )
}