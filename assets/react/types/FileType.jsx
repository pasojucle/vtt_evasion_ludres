import { idFromName } from "../utils/type"

export default function FileType({label, name, filename, handleChange, col}) {

    const id = idFromName(name);

    return (
        <div className={`col-span-2 sm:col-span-${col}`}>
            <label htmlFor={id} className="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{label}</label>
            <input onChange={(e) => {handleChange(e.target.files[0])}} id={id} name={name} type="file" filename={filename}
                className="w-full text-slate-500 font-medium text-base bg-gray-50 file:cursor-pointer cursor-pointer file:border-0 file:py-2.5 file:px-4 file:mr-4 file:bg-gray-50 dark:file:bg-gray-600 file:hover:bg-gray-700 file:text-white rounded-lg" />
        </div>
    )
}