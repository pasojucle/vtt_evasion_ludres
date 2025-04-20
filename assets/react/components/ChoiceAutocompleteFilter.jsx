import React, { useState, useRef } from 'react';
import { toString } from '../utils'


export default function ChoiceAutocompleteFilter({list, value, label, className, placeholder, handleAdd, handleRemove}) {

    const [textFilter, setTextFilter] = useState('');
    const [focused, setFocused] = useState(false);
    const [idActive, setIdActive] = useState(null);
    const inputRef = useRef(null);
    const multiple = value instanceof Array;

    const itemObjectFromValue = (value) => {
        return list.find((item) => item.id === value);
    }

    const input = (event) => {
        setFocused(true);
        setTextFilter(event.target.value.toLowerCase());
    }

    const add = (itemId) => {
        const itemObject = itemObjectFromValue(itemId)
        handleAdd(itemObject);
        itemObject.selected = true;
        setIdActive(itemObject.id);
        clearTextFilter();
    }

    const remove = (itemObject) => {
        clearTextFilter();
        itemObject.selected = false;
        handleRemove(itemObject);
        setIdActive(null);
    }

    const clear = () => {
        clearTextFilter();
        list.map((item) => item.selected = false);
        handleRemove();
        setIdActive(null);
    }

    const clearTextFilter = () => {
        setTextFilter('');
        setFocused(false);
    }

    const handleKeyDown = (event) => {
        const choiceList = choices();
        let index = choiceList.findIndex((item) => item.id === idActive)
        if ('Enter' === event.code && true !== choiceList[index].selected) {
            add(list[index].id)
            return;
        }
        if ('ArrowDown' === event.code) {
            ++index;
            if (index < choiceList.length) {
                setIdActive(choiceList[index].id);
                return;
            }
        }
        if ('ArrowUp' === event.code) {
            if (0 < index) {
                --index;
                setIdActive(choiceList[index].id);
                return;
            }
        }
        setIdActive(choiceList[0].id);
    }

    const choices = () => {       
        if ('' === textFilter) {
            return list;
        } 
  
        return list.filter((item) => toString(item).toLowerCase().includes(textFilter))
    }

    const ControlContent = () => {
        if (isEmpty()) {
            return;
        }
        if (multiple) {
            return(
                value.map((item) => 
                    <div key={item.id} className='af-multiple'>
                        <div className='af-content'>
                            { toString(item)}
                        </div>
                        <button className="btn af-btn" onClick={() => remove(item)}><i className="bi bi-x"></i></button>
                    </div>
                )
            )
        }
  
        return (
            <div className='af-content'>
                { toString(value)}
            </div>
        )
    }

    const ControlBtnClear = () => {
        if (!multiple && null !== value) {
            return (
                <button className="btn af-btn" onClick={clear}><i className="bi bi-x"></i></button>
            )
        }
    }

    const optionClassName = (item) => {
        let className = 'af-option';
        if (idActive === item.id) {
            className = className + ' active';
        }
        if (item.group) {
            className += ' af-option-group';
        } 
        if (item.selected) {
            className += ' af-selected';
        } 

        return className;
    }

    const Dropdown = () => {
        if (true === focused) {
            return (
                <div className="af-dropdown">
                    {choices().map((item, index) =>
                        <Item key={index} listItem={item}/>
                    )}
                </div>
            )
        }
    }

    const Item = ({listItem}) => {
        if (listItem.label) {
            return (
                <div className="af-group-label">{listItem.label}</div>
            )
        }
        return (
            <div key={listItem.id} className={ optionClassName(listItem)} onMouseDown={() => add(listItem.id)}>
                {toString(listItem)}
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

    const handleClick = (target) => {
        if (target.classList.contains('af-control')) {
            inputRef.current.focus();
        }
    }

    const isEmpty = () => {
        if (multiple) {
            return 0 === value.length;
        }

        return null === value;
    }

    const classWrapper = "autocomplete-filter " + className;
    const placeholderContent = (isEmpty()) ? placeholder : '';
    return (
        <div className={ classWrapper }>
            <Label/>
            <div className="af-wrapper">
                <div className="af-control" onClick={(event) => {handleClick(event.target)}}>
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