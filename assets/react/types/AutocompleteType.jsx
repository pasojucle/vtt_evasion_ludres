import { useState, useRef } from 'react';
import { ChevronDown, X, SquarePlus } from 'lucide-react';


export default function AutocompleteType({ list, value, label, className, placeholder, handleSelect, handleRemove }) {

    const [textFilter, setTextFilter] = useState('');
    const [dropDowCollapsed, setDropDowCollapsed] = useState(true);
    const [idActive, setIdActive] = useState(value && undefined !== value.id ? value.id : null);
    const inputRef = useRef(null);
    const multiple = value instanceof Array;

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
        setDropDowCollapsed(false);
        setTextFilter(event.target.value.toLowerCase());
    }

    const select = (itemId) => {
        if (!multiple && idActive) {
            const itemActiveObject = itemObjectFromValue(idActive)
            itemActiveObject.selected = false;
        }
        const itemObject = itemObjectFromValue(itemId)
        handleSelect(itemObject);
        itemObject.selected = true;
        setIdActive(itemObject.id);
        clearTextFilter();
    }

    const add = (e) => {
        const item = {
            id: 0,
            title: textFilter,
            selected: true,
        };
        handleSelect(item)
        console.log('added ---', item)
        setIdActive(0);
        clearTextFilter();
        setDropDowCollapsed(true);
        setAdded(true);
    }

    const remove = (itemObject) => {
        clearTextFilter();
        itemObject.selected = false;
        handleRemove(itemObject);
        setIdActive(null);
        setAdded(false);
    }

    const clear = () => {
        console.log('--- clear ---')
        clearTextFilter();
        list.map((item) => item.selected = false);
        handleRemove();
        setIdActive(null);
        setAdded(false);
    }

    const clearTextFilter = () => {
        setTextFilter('');
        setDropDowCollapsed(true);
    }

    const handleKeyDown = (event, choices) => {
        let index = choices.findIndex((item) => item.id === idActive)
        console.log('index', index);
        if ('Enter' === event.code) {
            event.preventDefault();
            if (true !== choices[index].selected) {
                select(list[index].id)
            }
            
            inputRef.current.blur();
            return;
        }
        if ('ArrowDown' === event.code) {
            ++index;
            if (index < choices.length) {
                setIdActive(choices[index].id);
                return;
            }
        }
        if ('ArrowUp' === event.code) {
            if (0 < index) {
                --index;
                setIdActive(choices[index].id);
                return;
            }
        }
        if (0 < choices.length) {
           setIdActive(choices[0].id); 
        }
    }

    const choiceList = () => {
        if ('' === textFilter) {
            return list;
        }

        return list.filter((item) => toString(item).toLowerCase().includes(textFilter));
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
                        <button onClick={() => remove(item)}><X size={14}/></button>
                    </div>
                )
            )
        }

        return (
            <div className='mr-1 px-1 box-border overflow-hidden text-ellipsis whitespace-nowrap'>
                {toString(value)}
            </div>
        )
    }

    const ControlAction = () => {
        if (0 === choices.length && 0 < textFilter.length) {
            return (
                <button type="button" className="py-2 hover:cursor-pointer" onClick={add} onMouseOver={() => setDropDowCollapsed(true)}>
                    <SquarePlus size={14}/>
                </button>
            )
        }

        if (!multiple && undefined !== value.id) {
            return (
                <button type="button" className="py-2 hover:cursor-pointer" onClick={clear} onMouseOver={() => setDropDowCollapsed(true)}>
                    <X size={14}/>
                </button>
            )
        }
    }

    const optionClassName = (item) => {
        let className = 'py-1 px-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-800';

        if (idActive === item.id) {
            className = className + ' bg-gray-200 dark:bg-gray-800';
        }
        if (item.group) {
            className += ' ps-3';
        }
        if (item.selected) {
            className += ' bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-gray-200';
        }

        return className;
    }

    const Dropdown = ({choices}) => {
        if (false === dropDowCollapsed) {
            return (
                <div className="absolute top-full bg-gray-100 dark:bg-gray-500 w-full p-1 rounded-b-md left-0 z-[99] border border-gray-300 dark:border-gray-500">
                    <DropDownItems choices={choices}/>
                </div>
            )
        } 
    }

    const DropDownItems = ({choices}) => {
        if (0 === choices.length) {
            return (
                <div>Auncun résultat</div>
            )
        }
        return(
            <>
                {choices.map((choice, index) =>
                    <Item key={index} listItem={choice} />
                )}
            </>
        )
    }

    const Item = ({ listItem }) => {
        if (listItem.label) {
            return (
                <div className="py-1 px-2 font-bold">{listItem.label}</div>
            )
        }
        return (
            <div key={listItem.id} className={optionClassName(listItem)} onMouseDown={() => select(listItem.id)}>
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

    const handleClick = () => {
        inputRef.current.focus();
    }

    const isEmpty = () => {
        if (multiple) {
            return 0 === value.length;
        }

        return 0 === Object.keys(value).length;
    }

    const classWrapper = "autocomplete-filter " + className;
    const placeholderContent = (isEmpty()) ? placeholder : '';
    const choices = choiceList();
    const componentRound = (false === setDropDowCollapsed && 0 < choices.length) ? 'rounded-t-lg' : 'rounded-lg';

    return (
        <div className={classWrapper}>
            <Label />
            <div className={`relative p-1 bg-gray-50 border ${componentRound} border-gray-300 text-gray-900 text-sm  focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white`}>
                <div className="flex flex-nowrap items-center wra w-full z-[1]" onClick={(event) => { handleClick(event.target) }}>
                    <ControlContent />
                    <input
                        className="border-none focus:outline-none min-w-[10px]"
                        ref={inputRef}
                        placeholder={placeholderContent}
                        value={textFilter}
                        onInput={input}
                        onFocus={() => setDropDowCollapsed(false)}
                        onBlur={() => setDropDowCollapsed(true)}
                        onKeyDown={(e) => handleKeyDown(e, choices)}
                    />
                    <div className="ml-auto bg-transparent px-[1px] flex flex-nowrap">
                        <ControlAction choices={choices} />
                        <button type="button" className="py-2 text-gray-800 dark:text-gray-200 hover:cursor-pointer" onClick={() => setDropDowCollapsed(false)}>
                            <ChevronDown size={14} />
                        </button>
                    </div>
                </div>
                <Dropdown choices={choices} />
            </div>
        </div>
    );
}