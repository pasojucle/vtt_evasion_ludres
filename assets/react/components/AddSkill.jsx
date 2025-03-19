import React, { useState } from 'react';
import { checkStatus, isJsonResponse } from './../../js/fetch.js'
import { formElement } from '../utils.js';
import EntityAutocompleteFilter from '../components/EntityAutocompleteFilter';

export default function AddSkill({edit, size, route, handleEditChange, update, mainList}) {

    const [title, setTitle] = useState('');
    const [theme, setTheme] = useState(null);
    const [form, setForm] = useState({});
    const [loaded, setLoaded] = useState(false);
    const [category, setCategory] = useState(null);
    const [clearCategory, setClearCategory] = useState(false);
    const [level, setLevel] = useState(null);
    const [clearLevel, setClearLevel] = useState(false);

    const load = () => {
        if (edit && route && !loaded) {
            fetch(route)
                .then(checkStatus)
                .then(isJsonResponse)
                .then((response) => response.json())
                .then((json)=> {
                    console.log('fetch edit', json)
                    setForm(json.form);
                    setTitle(json.title);
                    setTheme(json.theme);
                    setLoaded(true);
                });
        }
    }

    const onSubmit = (event) => {
        event.preventDefault();
        const form = event.target;
        console.log('submit', form)
        Array.from(form.elements).forEach((element) => {
            if ('-1' === element.value) {
                element.value = null;
            }
        })
        fetch(form.action, {
            method: 'POST',
            body : new FormData(form),
        })
        .then(checkStatus)
        .then(isJsonResponse)
        .then((response) => response.json())
        .then((json)=> {
            if (json.success) {
                update(json.data)
                hide(); 
            }
            if (json.form) {
                setForm(json.form);
            }
        });
    }
    const hide = () => {
        handleEditChange(false);
        setTimeout(() => {
            setLoaded(false);
            setForm({});
        }, 500);
    }


    const handleChangeCategory = (value) => {
        setCategory(value)
    }

    const handleChangeClearCategory = (value) => {
        setClearCategory(value)
    }

    const handleChangeLevel = (value) => {
        setLevel(value)
    }

    const handleChangeClearLevel = (value) => {
        setClearLevel(value)
    }

    const filters = () => {
        return {
            'category': category,
            'level': level,
        }
    }

    const ModalContent = () => {
        load();
        if (loaded) {
            return (
                <div className="modal-content">
                    <div className="modal-header">
                        <button type="button" className="close" onClick={hide} aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 className="modal-title">{ title }</h4>
                    </div>
                    <form action={form.action} onSubmit={(event) => onSubmit(event)}>
                        <div className="modal-body">
                            <div className="row">
                                <EntityAutocompleteFilter entityName="skill_category" pararms={[]} value={category} label="Catégorie" placeholder="Toutes les catégories" handleChange={handleChangeCategory} isClear={clearCategory} handleClear={handleChangeClearCategory} className="col-md-6 form-group"/>
                                <EntityAutocompleteFilter entityName="level" pararms={[]} value={level} label="Niveau" placeholder="Toutes les niveaux" handleChange={handleChangeLevel} isClear={clearLevel} handleClear={handleChangeClearLevel}  className="col-md-6 form-group"/>
                            </div>
                            <div className="row">
                                {form.components.map((component, key) => 
                                    formElement(component, key, false, filters(), mainList)
                                )}
                            </div>
                        </div>
                        <div className="modal-footer">
                            <button type="button" className="btn btn-default" onClick={hide}>Annuler</button>
                            <button type="submit" className={'btn ' + theme}>{ form.submit }</button>
                        </div>
                    </form>
                </div>
            )
        }
    }

    return (<div className={(edit) ? 'modal fade' : 'modal'} tabIndex="-1" role="dialog">           
        <div className={(edit) ? 'modal-dialog modal-open ' + size : 'modal-dialog '+ size } role="document">
            <ModalContent />
        </div>
    </div>)
}
