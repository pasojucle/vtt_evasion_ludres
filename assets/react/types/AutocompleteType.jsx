import React, { useState, useRef, useEffect } from 'react';
import { dataLoader } from '../helpers/queryHelper'


export default function AutocompleteType({ resource, value, label, className, placeholder, handleAdd, handleRemove }) {

    const [list, setList] = useState([]);
    const [textFilter, setTextFilter] = useState('');
    const [focused, setFocused] = useState(false);
    const [idActive, setIdActive] = useState(value && undefined !== value.id ? value.id : null);
    const inputRef = useRef(null);
    const multiple = value instanceof Array;

    useEffect(() => {
        const list = dataLoader(resource)
            .then((result) => setList(result.data.member))

    }, [resource])

    const toString = (entity) => {
        let string;
        switch (true) {
            case undefined !== entity.title:
                string = entity.title;
                break;
            default:
                string = entity.name;
        }
        return string;
    }

    const itemObjectFromValue = (value) => {
        return list.find((item) => item.id === value);
    }

    const input = (event) => {
        setFocused(true);
        setTextFilter(event.target.value.toLowerCase());
    }

    const add = (itemId) => {
        if (!multiple && idActive) {
            const itemActiveObject = itemObjectFromValue(idActive)
            itemActiveObject.selected = false;
        }
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
            event.preventDefault();
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
            return (
                value.map((item) =>
                    <div key={item.id} className="flex gap-2 items-center bg-gray-50 dark:bg-gray-600">
                        <div className='mr-1 box-border overflow-hidden text-ellipsis whitespace-nowrap px-1'>
                            {toString(item)}
                        </div>
                        <button className="btn af-btn" onClick={() => remove(item)}><i className="bi bi-x"></i></button>
                    </div>
                )
            )
        }

        return (
            <div className='mr-1 px-2 box-border overflow-hidden text-ellipsis whitespace-nowrap'>
                {toString(value)}
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
        let className = 'py-1 px-2';

        if (idActive === item.id) {
            className = className + ' cursor-pointer bg-gray-200 dark:bg-gray-800';
        }
        if (item.group) {
            className += ' ps-3';
        }
        if (item.selected) {
            className += ' bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-gray-200';
        }

        return className;
    }

    const Dropdown = () => {
        if (true === focused) {
            return (
                <div className="absolute bg-gray-100 dark:bg-gray-500 w-full p-1 rounded-b-md left-0 z-[99] ">
                    {choices().map((item, index) =>
                        <Item key={index} listItem={item} />
                    )}
                </div>
            )
        }
    }

    const Item = ({ listItem }) => {
        if (listItem.label) {
            return (
                <div className="py-1 px-2 font-bold">{listItem.label}</div>
            )
        }
        return (
            <div key={listItem.id} className={optionClassName(listItem)} onMouseDown={() => add(listItem.id)}>
                {toString(listItem)}
            </div>
        )
    }

    const Label = () => {
        if (label) {
            return (
                <label className="block mb-2 text-sm font-medium text-gray-900 dark:text-white" >{label}</label>
            )
        }
    }

    const handleClick = (target) => {
        inputRef.current.focus();
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
        <div className={classWrapper}>
            <Label />
            <div className="relative p-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white">
                <div className="flex items-center w-full z-[1]" onClick={(event) => { handleClick(event.target) }}>
                    <ControlContent />
                    <input
                        className='border-none min-w-[10px]'
                        ref={inputRef}
                        type="search"
                        placeholder={placeholderContent}
                        value={textFilter}
                        onInput={input}
                        onFocus={() => setFocused(true)}
                        onBlur={() => setFocused(false)}
                        onKeyDown={handleKeyDown}
                    />
                    <div className="ml-auto bg-transparent px-[1px]">
                        <ControlBtnClear />
                        <button type="button" className="py-2 px-1 text-gray-800 dark:text-gray-200" onClick={() => setFocused(true)}>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-4">
                                <path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
                <Dropdown />
            </div>
        </div>
    );
}