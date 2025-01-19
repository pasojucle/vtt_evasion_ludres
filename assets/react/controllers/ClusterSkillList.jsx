import React, { useState, useEffect } from 'react';
import Routing from 'fos-router';
import AddSkill from '../components/AddSkill';

import { getList } from '../utils';

export default function ClusterSkillList({cluster, canEdit}) {

    const [clusterSkillList, setClusterSkillList] = useState([]);

    const [edit, setEdit] = useState(false);
    const [route, setRoute] = useState(null);

    useEffect(() => {
        const list = getList('api_cluster_skill_list', {'cluster': cluster})
            .then((list) => setClusterSkillList(list))
    }, [])

    const handleAdd = () => {
        setRoute(Routing.generate('api_cluster_skill_add', {'cluster': cluster}));
        setEdit(true);
    }

    const handleEval = (id) => {
        setRoute(Routing.generate('api_skill_eval', {'cluster': cluster, 'skill': id}));
        setEdit(true);
    }

    const handleDelete = (id) => {
        setRoute(Routing.generate('api_skill_delete', {'cluster': cluster, 'skill': id}));
        setEdit(true);
    }

    const handleEditChange = (value) => {
        setEdit(value)
    }
    
    const createMarkup = (plainText) => {
        return {__html: plainText};
    }

    const updateList = (data) => {
        const list = clusterSkillList;
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
        setClusterSkillList(list);
    }

    return (
        <div>
            <a className="btn btn-primary" onClick={handleAdd} title="Ajouter"> Ajouter</a>

            <ul>
                {clusterSkillList.map((skill) => 
                    <li className="list-group-item" key={skill.id}>
                        <div dangerouslySetInnerHTML={createMarkup(skill.content)} />
                        <div className="dropdown">
                            <button className="dropdown-toggle" type="button" data-toggle="dropdown-tools"></button>
                            <div className="dropdown-menu" data-target="dropdown-tools">
                                <ul className="dropdown-body">
                                    <li>
                                        <a className="dropdown-item" onClick={() => handleEval(skill.id)} title="Évaluations"><i className="fa-solid fa-graduation-cap"></i> Évaluations</a>
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
            <AddSkill edit={edit} route={route} size="lg" handleEditChange={handleEditChange} update={updateList} mainList={clusterSkillList} />
        </div>
    )

}

