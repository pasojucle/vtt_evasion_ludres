import React, { useState, useEffect } from 'react';
import Routing from 'fos-router';
import Edit from '../components/Edit';
import AddSkill from '../components/AddSkill';

import { getList, updateList } from '../utils';

export default function ClusterSkillList({cluster, canEdit}) {

    const [clusterSkillList, setClusterSkillList] = useState([]);

    const [edit, setEdit] = useState(false);
    const [add, setAdd] = useState(false);
    const [route, setRoute] = useState(null);

    useEffect(() => {
        const list = getList('api_cluster_skill_list', {'cluster': cluster})
            .then((list) => setClusterSkillList(list))
    }, [])

    const handleAdd = () => {
        setRoute(Routing.generate('api_cluster_skill_add', {'cluster': cluster}));
        setAdd(true);
    }

    const handleEval = (id) => {
        setRoute(Routing.generate('api_cluster_skill_eval', {'cluster': cluster, 'skill': id}));
        setEdit(true);
    }

    const handleDelete = (id) => {
        setRoute(Routing.generate('api_cluster_skill_delete', {'cluster': cluster, 'skill': id}));
        setEdit(true);
    }

    const handleEditChange = (value) => {
        setEdit(value)
    }

    const handleAddChange = (value) => {
        setAdd(value)
    }
    
    const createMarkup = (plainText) => {
        return {__html: plainText};
    }

    const update = (data) => {
        setClusterSkillList(updateList(clusterSkillList, data));
    }

    return (
        <div>
            <a className="btn btn-primary" onClick={handleAdd} title="Ajouter"> Ajouter</a>

            <ul className='list-group'>
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
            <Edit edit={edit} route={route} size="lg" handleEditChange={handleEditChange} update={update}/>
            <AddSkill edit={add} route={route} size="lg" handleEditChange={handleAddChange} update={update} mainList={clusterSkillList} />
        </div>
    )
}

