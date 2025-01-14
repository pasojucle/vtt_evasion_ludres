import React, { useState, useEffect } from 'react';
import Routing from 'fos-router';
import {toString} from '../../js/utils'

export default function AutocompleteFilter({entity, params, value,  label, className, placeholder, handleChange, isClear= false, handleClear}) {

    const [textFilter, setTextFilter] = useState('');
    const [focused, setFocused] = useState(false);
    const [itemActive, setItemActive] = useState(0);
    const [list, setList] = useState([]);

    useEffect(() => {
        fetch(Routing.generate(`api_${entity}_list`, params), {
        method: "GET", 
        })
        .then(response => response.json())
        .then(json => {
            console.log('json',entity, json)
            setList(json.list);
        });
    }, [])

    const input = (event) => {
        setTextFilter(event.target.value.toLowerCase());
    }

    const change = (value) => {
        setFocused(false);
        handleClear(false);
        setTextFilter('');
        handleChange(value);
    }

    const clear = () => {
        setTextFilter('');
        handleClear(true);
        handleChange(0);
    }

    const handleKeyDown = (event) => {
        if ('Enter' === event.code) {
            change(listFiltered()[itemActive].id)
        }
        if ('ArrowDown' === event.code) {
            const length = listFiltered().length;
            const index = itemActive + 1;
            if (index < length) {
                setItemActive(index);
            }
        }
        if ('ArrowUp' === event.code) {
            if (0 < itemActive) {
                const index = itemActive - 1;
                setItemActive(index);
            }
        }
    }

    const listFiltered = () => {
        if ('' === textFilter) {
            return list;
        }
        return list.filter((item) => item.name.toLowerCase().includes(textFilter))
    }

    const displayControlContent = () => {
        return '' !== value && null !== value && !isClear;
    }

    const ControlContent = () => {
        if (displayControlContent()) {
            const item =  list.find((item) => item.id === value);
            const text =  (item) ? toString(item): '';
            return (
                <div className="af-content">{ text }</div>
            )
        }
    }
    const ControlBtn = () => {
        if (displayControlContent()) {
            return (
                <div className="ms-auto" style={{width: "35px"}}>
                    <button className="btn af-btn" onClick={clear}><i className="bi bi-x"></i></button>
                    <button className="btn af-btn" onClick={() => setFocused(true)}><i className="bi bi-caret-down-fill"></i></button>
                </div>
            )
        }
    }

    const optionClassName = (index) => {
        if (itemActive === index) {
            return 'af-option active';
        }
        return 'af-option';
    }

    const Dropdown = () => {
        if (focused) {
            return (
                <div className="af-dropdown">
                    {listFiltered().map((item, index) =>
                        <div key={item.id} className={ optionClassName(index)} onMouseDown={() => change(item.id)}>
                            {toString(item)}
                        </div>
                    )}
                </div>
            )
        }
    }
    const classWrapper = "autocomplete-filter " + className;
    const placeholderContent = (value) ? '' : placeholder;
    return (
        <div className={ classWrapper }>
            <label className="form-label" >{ label }</label>
            <div className="af-wrapper">
                <div className="af-control">
                    <ControlContent/>
                    <input
                        type="search"
                        placeholder={placeholderContent}
                        value={textFilter}
                        onInput={input}
                        onFocus={() => setFocused(true)}
                        onBlur={() => setFocused(false)}
                        onKeyDown={handleKeyDown}
                    />
                    <ControlBtn/>
                </div>
                <Dropdown/>
            </div>
        </div>
    );
}