import { idFromName } from "../utils/type"

export default function TextType({label, name, value, handleChange, col}) {

    const id = idFromName(name);
    return (
        <div className={`col-span-${col} sm:col-span-1`}>
            <label htmlFor={id} className="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{label}</label>
            <input type="text" name={name} id={id} value={value} onChange={handleChange} className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required />
        </div>
    )
}