import { idFromName } from "../utils/type"

export default function CheckboxType({label, name, checked, handleChange, col}) {
    const id = idFromName(name);
        // <div className={`col-span-2 sm:col-span-${col}`}>
        //     <label className="relative inline-block w-[30px] h-[14px]">
        //         <input className="peer invisible" type="checkbox" id={id} name={name} value={value} onClick={handleClick}/>
        //         <span className="absolute top-0 left-0 rigth-0 bottom-0 transition duration-400 bg-gray-200 dark:bg-gray-800 before:absolute before:content-none before:h-[10px] before:w-[10px] before:left-[2px] before:bottom-2-[px] before:rounded-[50%] rounded-[15px] bg-color-red-500 peer-checked:bg-color-green-500"></span>
        //     </label>
        //     <label htmlFor={id}>{label}</label>
        // </div>
    return (
        <div className={`col-span-2 sm:col-span-${col}`}>
            <label htmlFor={id} className="inline-flex items-center cursor-pointer">
                <input type="checkbox" id={id} name={name} checked={checked} onChange={() => handleChange(!checked)} className="sr-only peer"/>
                <div className="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600"></div>
                <span className="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">{label}</span>
            </label>
        </div>

    )
}