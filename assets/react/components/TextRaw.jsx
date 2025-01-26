import React from 'react';

export default function TextRaw({textHtml}) {

    const createMarkup = () => {
        console.log('createMarkup', textHtml)
        return {__html: textHtml};
    }

    return (
        <div dangerouslySetInnerHTML={createMarkup()} />
    )
}