import React, { useState } from 'react';
import TextType from './TextType';


export default function Parameters({data}) {
    const entity = 'parameters';
    const [projectName, setProjectName] = useState(null);
    const [theme, setTeme] = useState(null);
    const [favicon, setFavicon] = useState(null);
    const [encryption, setEncryption] = useState(false);

    const changeProjectName = (value) => {
        setProjectName(value);
    }

    const FormElement = ({element}) => {
        console.log('formElement', element.name)
        switch(element.name) {
            case 'PROJECT_NAME':
                return React.createElement(TextType, {label: element.label , name: `${entity}[${element.name}]`, value: element.value, handleChange: changeProjectName, col: 1});
                break;
        }
    }

    if (data) {
        return (
            <form className="space-y-4" action="#">
                <div className="mb-4">
                    {data.member.map((element) => 
                        <FormElement key={element.name} element={element}/>
                    )}
                </div>        
                <button type="submit" className="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Enregistrer</button>
            </form>
        )
    }
}