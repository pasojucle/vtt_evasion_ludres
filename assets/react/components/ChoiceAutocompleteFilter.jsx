import React, { useState, useEffect } from 'react';
import { toString } from '../utils'


export default function ChoiceAutocompleteFilter({list, value,  label, className, placeholder, handleChange, isClear= false, handleClear}) {

    const [textFilter, setTextFilter] = useState('');
    const [focused, setFocused] = useState(false);
    const [itemActive, setItemActive] = useState(0);

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
        handleChange(null);
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
        console.log('choiceAutocomplete list', list)
        console.log('choiceAutocomplete textFilter', textFilter)
        if ('' === textFilter) {
            return list;
        }

        return list.filter((item) => toString(item).toLowerCase().includes(textFilter))
    }

    const displayControlContent = () => {
        return null !== value && !isClear;
    }

    const ControlContent = () => {
        if (displayControlContent()) {
            const item = list.find((item) => item.id === value);
            const text = (item) ? toString(item): '';
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
                        <Item key={index} listItem={item} listIndex={index}/>
                    )}
                </div>
            )
        }
    }

    const Item = ({listItem, listIndex}) => {
        if (listItem instanceof Object) {
            return (
                <Option item={listItem} index={listIndex}/>
            )
        }
        if (listItem instanceof Array) {
            return (
                <optgroup>
                    {listItem.map((item, index) =>
                        <Option item={item} index={`${listIndex}-${index}`}/>
                    )}
                </optgroup>
            )
        }
        console.log('---------------')
    }
    const Option = ({item, index}) => {
        return (
            <div key={item.id} className={ optionClassName(index)} onMouseDown={() => change(item.id)}>
                {toString(item)}
            </div>
        )
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
                        // onBlur={() => setFocused(false)}
                        onKeyDown={handleKeyDown}
                    />
                    <ControlBtn/>
                </div>
                <Dropdown/>
            </div>
        </div>
    );
}