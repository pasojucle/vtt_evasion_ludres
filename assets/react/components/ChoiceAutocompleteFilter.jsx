import React, { useState, useRef } from 'react';
import { toString } from '../utils'


export default function ChoiceAutocompleteFilter({list, value, label, className, placeholder, handleAdd, handleRemove}) {

    const [textFilter, setTextFilter] = useState('');
    const [focused, setFocused] = useState(false);
    const [indexActive, setIndexActive] = useState(0);
    const inputRef = useRef(null);
    const multiple = value instanceof Array;

    const itemObjectFromValue = (value) => {
        return list.find((item) => item.id === value);
    }

    const input = (event) => {
        setFocused(true);
        setTextFilter(event.target.value.toLowerCase());
    }

    const add = (item) => {
        setFocused(false);
        setTextFilter('');
        handleAdd(itemObjectFromValue(item));
    }

    const remove = (item) => {
        handleRemove(item);
    }

    const clear = () => {
        setTextFilter('');
        handleRemove();
    }

    const handleKeyDown = (event) => {
        if ('Enter' === event.code) {
            add(listFiltered()[indexActive].id)
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
        if ('' === textFilter) {
            return list;
        }

        return list.filter((item) => toString(item).toLowerCase().includes(textFilter))
    }

    const ControlContent = () => {
        if (multiple && 0 < value.length) {
            return(
                value.map((item) => 
                    <div key={item.id} className='af-multiple'>
                        <div className='af-content'>
                            { (value) ? toString(item): '' }
                        </div>
                        <button className="btn af-btn" onClick={() => remove(item)}><i className="bi bi-x"></i></button>
                    </div>
                )
            )
        }
        if (null !== value) {
            return (
                <div className='af-content'>
                    { (value) ? toString(value): '' }
                </div>
            )
        }
    }

    const ControlBtnClear = () => {
        if (!multiple && null !== value) {
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
            <div key={item.id} className={ optionClassName(item, index)} onMouseDown={() => add(item.id)}>
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

    const handleClick = () => {
        inputRef.current.focus();
    }

    const classWrapper = "autocomplete-filter " + className;
    const placeholderContent = (value) ? '' : placeholder;
    return (
        <div className={ classWrapper }>
            <Label/>
            <div className="af-wrapper">
                <div className="af-control" onClick={handleClick}>
                    <ControlContent/>
                    <input
                        ref={inputRef} 
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