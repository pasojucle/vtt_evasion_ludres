import React, { useState, useEffect } from 'react';
import { getList } from '../utils'
import AutocompleteFilter from '../components/AutocompleteFilterType';
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

    const createMarkup = (plainText) => {
        return {__html: plainText};
    }

    const updateList = (data) => {
        const list = skillList;
        const index = list.findIndex(item => {
            return (data.value.id === item.id)
          })
          console.log('index', index, data.value);
          switch(true) {
            case -1 === index:
              console.log('add')
              list.push(data.value);
              break;
            case data.deleted:
              console.log('delete')
              list.splice(index, 1);
              break;
            default:
              console.log('update')
              list.splice(index, 1, data.value)
          }
        setSkillList(list);
    }

    return (
        <div>
            <div className="row">
                <AutocompleteFilter entityName="skill_category" pararms={[]} value={category} label="Catégorie" placeholder="Toutes les catégories" handleChange={handleChangeCategory} isClear={clearCategory} handleClear={handleChangeClearCategory} className="col-md-6 form-group"/>
                <AutocompleteFilter entityName="level" pararms={[]} value={level} label="Niveau" placeholder="Toutes les niveaux" handleChange={handleChangeLevel} isClear={clearLevel} handleClear={handleChangeClearLevel}  className="col-md-6 form-group"/>
            </div>
            <a className="btn btn-primary" onClick={handleAdd} title="Ajouter"> Ajouter</a>

            <ul>
                {listFiltered().map((skill) => 
                    <li className="list-group-item" key={skill.id}>
                        <div dangerouslySetInnerHTML={createMarkup(skill.content)} />
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
            <Edit edit={edit} route={route} size="lg" handleEditChange={handleEditChange} update={updateList} />
        </div>
    )

}

