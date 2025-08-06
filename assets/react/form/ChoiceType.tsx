import { idFromName } from "@/utils/type"

type ChoiceProps = {
    label: string,
    name: string,
    value: any,
    options: any,
    handleChange: (value: any)=>void,
    col: number,
}
export default function ChoiceType({label, name, value, options, handleChange, col}: ChoiceProps): React.JSX.Element {

    const id = idFromName(name);

    const Option = ({option}:any) => {
        console.log('option', option)
        return (
            <option value={option.value}>{option.text}</option>
        )
    }

    return (
        <div className={`col-span-2 sm:col-span-${col}`}>
            <label htmlFor={id} className="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{label}</label>
            <select
                value={value}
                name={name}
                id={id}
                onChange={e => handleChange(e.target.value)} 
                className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                required 
            >
                {options.map((option:any) =>
                    <Option key={option.value} option={option}/>
                )}
      
             </select>
        </div>
    )
}