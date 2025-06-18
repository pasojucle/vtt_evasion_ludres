import React, { useState, useEffect } from 'react';
import { useModal } from '../hooks/useModal';
import { useAuth } from '../hooks/useAuth';
import { dataSender }  from "../helpers/queryHelper";

import TextType from './TextType';
import ChoiceType from './ChoiceType';
import CheckboxType from './CheckboxType';

export default function Parameters({data}) {
    const entity = 'parameters';
    const [projectName, setProjectName] = useState('');
    const [theme, setTheme] = useState('');
    const [favicon, setFavicon] = useState('');
    const [encryption, setEncryption] = useState(false);
    const { token } = useAuth();
    const { hide } = useModal();

    useEffect(() => {
        setTheme(data.member.find((element) => element.name === 'THEME_CSS').value);
        setFavicon(data.member.find((element) => element.name === 'FAVICON').value);
        setProjectName(data.member.find((element) => element.name === 'PROJECT_NAME').value);
        setEncryption(parseInt(data.member.find((element) => element.name === 'ENCRYPTION').value) !== 0);
    }, [])

    const changeProjectName = (value) => {
        setProjectName(value);
    }

    const changeTheme = (value) => {
        setTheme(value);
    }

    const changeEncryption = (value) => {
        console.log('changeEncryption', value)
        setEncryption(value);
        submit('ENCRYPTION', value ? '1' : '0')
    }

    const changeFavicon = (value) => {
        setFavicon(value);
    }

    const FormElement = ({element}) => {
        console.log('formElement', element.name)
        switch(element.name) {
            case 'PROJECT_NAME':
                return React.createElement(TextType, {label: element.label , name: `${entity}[${element.name}]`, value: projectName, handleChange: changeProjectName, col: 2});
            case 'THEME_CSS':
                const options = [
                    {value: 'ligth-theme', text: 'ligth-theme'},
                    {value: 'dark-theme', text: 'dark-theme'}
                ];
                console.log('theme', element.value)
                return React.createElement(ChoiceType, {label: element.label , name: `${entity}[${element.name}]`, value: theme, options: options, handleChange: changeTheme, col: 2});
            case 'ENCRYPTION':
                return React.createElement(CheckboxType, {label: element.label , name: `${entity}[${element.name}]`, checked: encryption, handleChange: changeEncryption, col: 2});


            case 'FAVICON':
                console.log('FAVICON')
        }
    }

    const submit = async(name, value) => {
        const data = JSON.stringify({value: value});

        dataSender('PATCH', 'parameters', name, token, data)
            .then(() => {

            })
    }

    if (data) {
        return (
            <form className="space-y-4" action="#">
                <div className="grid gap-4 mb-4 grid-cols-2">
                    {data.member.map((element) => 
                        <FormElement key={element.name} element={element}/>
                    )}
                </div>        
                <button type="button" onClick={hide} className="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Fermer</button>
            </form>
        )
    }
}