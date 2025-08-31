import React from 'react';

export default function TextRaw({textHtml, className=''}: {textHtml: string|undefined, className?: string}): React.JSX.Element | undefined {

    if (undefined !== textHtml) {
        const createMarkup = () => {
            return {__html: textHtml};
        }

        return (
            <div className={className} dangerouslySetInnerHTML={createMarkup()} />
        )
    }
}