import React, { useState, useEffect } from 'react';
import { getList, updateList } from '../utils.js'
import AutocompleteFilter from '../components/AutocompleteFilterType.jsx';
import TextRaw from '../components/TextRaw.jsx';
import Edit from '../components/Edit.jsx';
import Routing from 'fos-router';

export default function SkillCategoryList() {


    const [skillCategoryList, setSkillCategoryList] = useState([]);

    const [edit, setEdit] = useState(false);
    const [route, setRoute] = useState(null);

    useEffect(() => {
        const list = getList('api_skill_category_list')
            .then((list) => setSkillCategoryList(list))
    }, [])

    const handleAdd = () => {
        setRoute(Routing.generate('api_skill_category_add'));
        setEdit(true);
    }

    const handleEdit = (id) => {
        setRoute(Routing.generate('api_skill_category_edit', {'id': id}));
        setEdit(true);
    }

    const handleDelete = (id) => {
        setRoute(Routing.generate('api_skill_category_delete', {'id': id}));
        setEdit(true);
    }


    const handleEditChange = (value) => {
        setEdit(value)
    }

    const update = (data) => {
        setSkillCategoryList(updateList(skillCategoryList, data));
    }

    return (
        <div>
            <a className="btn btn-primary" onClick={handleAdd} title="Ajouter"> Ajouter</a>

            <ul className='list-group'>
                {skillCategoryList.map((skillCategory) => 
                    <li className="list-group-item" key={skillCategory.id}>
                        <div>{skillCategory.name}</div>
                        <div className="dropdown">
                            <button className="dropdown-toggle" type="button" data-toggle="dropdown-tools"></button>
                            <div className="dropdown-menu" data-target="dropdown-tools">
                                <ul className="dropdown-body">
                                    <li>
                                        <a className="dropdown-item" onClick={() => handleEdit(skillCategory.id)} title="Modifier"><i className="fas fa-pencil-alt"></i> Modifier</a>
                                    </li>
                                    <li>
                                        <a className="dropdown-item" onClick={() => handleDelete(skillCategory.id)} title="Supprimer" data-type="danger"><i className="fas fa-times"></i> Supprimer</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                )}
            </ul>
            <Edit edit={edit} route={route} size="lg" handleEditChange={handleEditChange} update={update} />
        </div>
    )

}

