import React, { useState, useEffect } from 'react';
import { toString } from '../utils'


export default function ChoiceAutocompleteFilter({list, value, label, className, placeholder, handleChange, isClear= false, handleClear}) {

    const [textFilter, setTextFilter] = useState('');
    const [focused, setFocused] = useState(false);
    const [indexActive, setIndexActive] = useState(0);

    const itemObjectFromValue = (value) => {
        return list.find((item) => item.id === value);
    }

    const input = (event) => {
        setTextFilter(event.target.value.toLowerCase());
    }

    const change = (value) => {
        setFocused(false);
        handleClear(false);
        setTextFilter('');
        const itemObject = itemObjectFromValue(value);
        handleChange(itemObject);
        console.log('change', value)
    }

    const clear = () => {
        setTextFilter('');
        handleClear(true);
        handleChange(null);
        console.log('clear')
    }

    const handleKeyDown = (event) => {
        if ('Enter' === event.code) {
            change(listFiltered()[indexActive].id)
        }
        if ('ArrowDown' === event.code) {
            const length = listFiltered().length;
            const index = indexActive + 1;
            if (index < length) {
                setIndexActive(index);
            }
        }
        if ('ArrowUp' === event.code) {
            if (0 < indexActive) {
                const index = indexActive - 1;
                setIndexActive(index);
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
            console.log('value', value);
            const text = (value) ? toString(value): '';
            return (
                <div className="af-content">{ text }</div>
            )
        }
    }
    const ControlBtnClear = () => {
        if (displayControlContent()) {
            return (
                <button className="btn af-btn" onClick={clear}><i className="bi bi-x"></i></button>
            )
        }
    }

    const optionClassName = (item, index) => {
        let className = 'af-option';

        if (indexActive === index) {
            className = className + ' active';
        }
        if (item.group) {
            className = className + ' af-option-group';
        } 

        return className;
    }

    const Dropdown = () => {
        if (focused) {
            return (
                <div className="af-dropdown">
                    {listFiltered().map((item, index) =>
                        <Item key={index} listItem={item} index={index}/>
                    )}
                </div>
            )
        }
    }

    const Item = ({listItem, index}) => {
        if (listItem.label) {
            return (
                <div className="af-group-label">{listItem.label}</div>
            )
        }
        return (
            <Option item={listItem} index={index}/>
        )
    }
    const Option = ({item, index}) => {
        return (
            <div key={item.id} className={ optionClassName(item, index)} onMouseDown={() => change(item.id)}>
                {toString(item)}
            </div>
        )
    }

    const Label = () => {
        if (label) {
            return (
                <label className="form-label" >{ label }</label>
            )
        }
    }

    const classWrapper = "autocomplete-filter " + className;
    const placeholderContent = (value) ? '' : placeholder;
    return (
        <div className={ classWrapper }>
            <Label/>
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
                    <div className="af-btn-group">
                        <ControlBtnClear/>
                        <button className="btn af-btn" onClick={() => setFocused(true)}><i className="bi bi-chevron-down"></i></button>
                    </div>
                </div>
                <Dropdown/>
            </div>
        </div>
    );
}