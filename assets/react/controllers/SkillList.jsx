import React, { useState, useEffect } from 'react';
import { getList, updateList } from '../utils'
import EntityAutocompleteFilter from '../components/EntityAutocompleteFilter';
import TextRaw from '../components/TextRaw.jsx';
import Edit from '../components/Edit';
import Routing from 'fos-router';

export default function SkillList() {

    const [category, setCategory] = useState(null);
    const [clearCategory, setClearCategory] = useState(false);
    const [level, setLevel] = useState(null);
    const [clearLevel, setClearLevel] = useState(false);
    const [skillList, setSkillList] = useState([]);

    const [edit, setEdit] = useState(false);
    const [route, setRoute] = useState(null);

    useEffect(() => {
        const list = getList('api_skill_list')
            .then((list) => setSkillList(list))
    }, [])

    const handleAdd = () => {
        setRoute(Routing.generate('api_skill_add'));
        setEdit(true);
    }

    const handleEdit = (id) => {
        setRoute(Routing.generate('api_skill_edit', {'id': id}));
        setEdit(true);
    }

    const handleDelete = (id) => {
        setRoute(Routing.generate('api_skill_delete', {'id': id}));
        setEdit(true);
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

    const handleEditChange = (value) => {
        setEdit(value)
    }

    const categoryFilter = (list) => {
        if (!category) {
            return list;
        }
        return list.filter((item) => item.category.id === category)
    }

    const levelFilter = (list) => {
        if (!level) {
            return list;
        }
        return list.filter((item) => item.level.id === level)
    }

    const listFiltered = () => {
        const list = categoryFilter(skillList);
        return levelFilter(list);
    }

    const update = (data) => {
        setSkillList(updateList(skillList, data));
    }

    return (
        <div>
            <div className="row">
                <EntityAutocompleteFilter entityName="skill_category" pararms={[]} value={category} label="Catégorie" placeholder="Toutes les catégories" handleChange={handleChangeCategory} isClear={clearCategory} handleClear={handleChangeClearCategory} className="col-md-6 form-group"/>
                <EntityAutocompleteFilter entityName="level" pararms={[]} value={level} label="Niveau" placeholder="Toutes les niveaux" handleChange={handleChangeLevel} isClear={clearLevel} handleClear={handleChangeClearLevel}  className="col-md-6 form-group"/>
            </div>
            <a className="btn btn-primary" onClick={handleAdd} title="Ajouter"> Ajouter</a>

            <ul className='list-group'>
                {listFiltered().map((skill) => 
                    <li className="list-group-item" key={skill.id}>
                        <TextRaw textHtml={skill.content} />
                        <div className="dropdown">
                            <button className="dropdown-toggle" type="button" data-toggle="dropdown-tools"></button>
                            <div className="dropdown-menu" data-target="dropdown-tools">
                                <ul className="dropdown-body">
                                    <li>
                                        <a className="dropdown-item" onClick={() => handleEdit(skill.id)} title="Modifier"><i className="fas fa-pencil-alt"></i> Modifier</a>
                                    </li>
                                    <li>
                                        <a className="dropdown-item" onClick={() => handleDelete(skill.id)} title="Supprimer" data-type="danger"><i className="fas fa-times"></i> Supprimer</a>
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

