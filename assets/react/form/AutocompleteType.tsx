import { useState, useRef } from 'react';
import { ChevronDown, X, SquarePlus } from 'lucide-react';

type AutocompleteProps = {
    list: any,
    value: any,
    label: string,
    className: string,
    placeholder: string,
    handleSelect: (itemObject: any)=>void,
    handleRemove: (itemObject?: any)=>void
}
export default function AutocompleteType({ list, value, label, className, placeholder, handleSelect, handleRemove }: AutocompleteProps) {

    const [textFilter, setTextFilter] = useState<string>('');
    const [dropDowCollapsed, setDropDowCollapsed] = useState<boolean>(true);
    const [idActive, setIdActive] = useState<number|null>(value && undefined !== value.id ? value.id : null);
    const inputRef = useRef<HTMLInputElement>(null);
    const multiple = value instanceof Array;

    const toString = (entity: any) => {
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

    const itemObjectFromValue = (value: any) => {
        return list.find((item: {id: number}) => item.id === value);
    }

    const input = (event: React.KeyboardEvent<HTMLInputElement>) => {
        setDropDowCollapsed(false);
        if (event.currentTarget) {
            setTextFilter(event.currentTarget.value.toLowerCase());
        }
    }

    const select = (itemId: number) => {
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

    const add = () => {
        const item = {
            id: 0,
            title: textFilter,
            selected: true,
        };
        handleSelect(item)
        setIdActive(0);
        clearTextFilter();
        setDropDowCollapsed(true);
    }

    const remove = (itemObject: any) => {
        clearTextFilter();
        itemObject.selected = false;
        handleRemove(itemObject);
        setIdActive(null);
    }

    const clear = () => {
        clearTextFilter();
        list.map((item: any) => item.selected = false);
        handleRemove();
        setIdActive(null);
    }

    const clearTextFilter = () => {
        setTextFilter('');
        setDropDowCollapsed(true);
    }

    const handleKeyDown = (event: React.KeyboardEvent<HTMLInputElement>, filteredChoices: any) => {
        let index = filteredChoices.findIndex((item:any) => item.id === idActive)
        if ('Enter' === event.code) {
            event.preventDefault();
            if (true !== filteredChoices[index].selected) {
                select(list[index].id)
            }
            
            inputRef.current?.blur();
            return;
        }
        if ('ArrowDown' === event.code) {
            ++index;
            if (index < filteredChoices.length) {
                setIdActive(filteredChoices[index].id);
                return;
            }
        }
        if ('ArrowUp' === event.code) {
            if (0 < index) {
                --index;
                setIdActive(filteredChoices[index].id);
                return;
            }
        }
        if (0 < filteredChoices.length) {
           setIdActive(filteredChoices[0].id); 
        }
    }

    const choiceList = () => {
        if ('' === textFilter) {
            return list;
        }

        return list.filter((item: string) => toString(item).toLowerCase().includes(textFilter));
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

    const ControlAction = ({choices}:any) => {
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

    const optionClassName = (item: any) => {
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

    const Dropdown = ({choices}:any) => {
        if (false === dropDowCollapsed) {
            return (
                <div className="absolute top-full bg-gray-100 dark:bg-gray-500 w-full p-1 rounded-b-md left-0 z-[99] border border-gray-300 dark:border-gray-500">
                    <DropDownItems choices={choices}/>
                </div>
            )
        } 
    }

    const DropDownItems = ({choices}: any) => {
        if (0 === choices.length) {
            return (
                <div>Auncun résultat</div>
            )
        }
        return(
            <>
                {choices.map((choice: any, index:number) =>
                    <Item key={index} listItem={choice} />
                )}
            </>
        )
    }

    const Item = ({ listItem }:any) => {
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
        if (inputRef.current) {
            inputRef.current.focus();
        }
    }

    const isEmpty = () => {
        if (multiple) {
            return 0 === value.length;
        }

        return 0 === Object.keys(value).length;
    }

    const classWrapper = "autocomplete-filter " + className;
    const placeholderContent = (isEmpty()) ? placeholder : '';
    const filteredChoices = choiceList();
    const componentRound = (false === dropDowCollapsed && 0 < filteredChoices.length) ? 'rounded-t-lg' : 'rounded-lg';

    return (
        <div className={classWrapper}>
            <Label />
            <div className={`relative p-1 bg-gray-50 border ${componentRound} border-gray-300 text-gray-900 text-sm  focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white`}>
                <div className="flex flex-nowrap items-center wra w-full z-[1]" onClick={handleClick}>
                    <ControlContent />
                    <input
                        className="border-none focus:outline-none min-w-[10px] grow"
                        ref={inputRef}
                        placeholder={placeholderContent}
                        value={textFilter}
                        onInput={input}
                        onFocus={() => setDropDowCollapsed(false)}
                        onBlur={() => setDropDowCollapsed(true)}
                        onKeyDown={(e) => handleKeyDown(e, filteredChoices)}
                    />
                    <div className="ml-auto bg-transparent px-[1px] flex flex-nowrap">
                        <ControlAction choices={filteredChoices} />
                        <button type="button" className="py-2 text-gray-800 dark:text-gray-200 hover:cursor-pointer" onClick={() => setDropDowCollapsed(false)}>
                            <ChevronDown size={14} />
                        </button>
                    </div>
                </div>
                <Dropdown choices={filteredChoices} />
            </div>
        </div>
    );
}