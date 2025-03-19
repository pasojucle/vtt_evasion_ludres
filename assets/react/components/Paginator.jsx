import React from 'react';

export default function Paginator({list, page, maxResults, handlePageChange, handleMaxResultsChange}) {

    const pages = () => {
        const content = [];
        const countPages = Math.ceil(list.length / maxResults);
        if (1 < page) {
            content.push(item(page - 1, '«'))
        }
        content.push(item(1))
        content.push(item(2))
        if (4 < countPages) {
            if (2 < page && page < countPages-1) {
                content.push(item('...'))
                content.push(item(page))
            }
            content.push(item('...'))
        }
        if (2 < countPages-1) {
            content.push(item(countPages-1))
        }
        if (2 < countPages) {
            content.push(item(countPages))
        }
        if (page < countPages) {
            content.push(item(page + 1, '»', ))
        }

        return content;
    }

    const item = (value, text = null) => {
        let className = (value === page) ? 'active' : 'page-item';
        if (value === '...') {
            className += ' disabled';
        }
        return {'className': className, 'textContent': text ?? value, 'value': value}
    }

    if (list.length < maxResults) {
        return
    }

    return (
        <div className="col-12 float-right flex flex-align-center mt-20">
            <div>
                <div className="input-prepend input-group">
                    <span className="add-on input-group-addon">Résultats</span>
                    <select
                        className="form-control"
                        value={maxResults}
                        onChange={(event) => handleMaxResultsChange(event.target.value)}
                    >
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <nav aria-label="pagination" className="ms-20">
                <ul className="pagination justify-content-center">
                    {pages().map((item, id) =>
                        <li key={id} className={item.className} onClick={() => handlePageChange(item.value)}><a
                            className="page-link">{item.textContent} </a></li>
                    )}
                </ul>
            </nav>
        </div>
    )

}