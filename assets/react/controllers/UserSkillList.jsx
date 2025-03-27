import React, { useState, useEffect } from 'react';
import { getData } from '../utils'
import EntityAutocompleteFilter from '../components/EntityAutocompleteFilter';
import TextRaw from '../components/TextRaw.jsx';

export default function UserSkillList({user}) {

    const [category, setCategory] = useState(null);
    const [clearCategory, setClearCategory] = useState(false);
    const [level, setLevel] = useState(null);
    const [clearLevel, setClearLevel] = useState(false);
    const [userSkillList, setUserSkillList] = useState([]);

    useEffect(() => {
        const list = getData('api_user_skill_list', {'user': user})
            .then((data) => setUserSkillList(data.list))
    }, [])

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

    const categoryFilter = (list) => {
        if (!category) {
            return list;
        }
        return list.filter((item) => item.skill.category.id === category)
    }

    const levelFilter = (list) => {
        if (!level) {
            return list;
        }
        return list.filter((item) => item.skill.level.id === level)
    }

    const listFiltered = () => {
        const list = categoryFilter(userSkillList);
        return levelFilter(list);
    }

    return (
        <div>
            <div className="row">
                <EntityAutocompleteFilter entityName="skill_category" pararms={[]} value={category} label="Catégorie" placeholder="Toutes les catégories" handleChange={handleChangeCategory} isClear={clearCategory} handleClear={handleChangeClearCategory} className="col-md-6 form-group"/>
                <EntityAutocompleteFilter entityName="level" pararms={[]} value={level} label="Niveau" placeholder="Toutes les niveaux" handleChange={handleChangeLevel} isClear={clearLevel} handleClear={handleChangeClearLevel}  className="col-md-6 form-group"/>
            </div>
            <ul className='list-group'>
                {listFiltered().map((userSkill) => 
                    <li className="list-group-item row" key={userSkill.id}>
                        <div className="col-md-2">{ userSkill.evaluateAt }</div>
                        <TextRaw textHtml={userSkill.skill.content} className="col-md-8"/>
                        <div className="col-md-2 text-center p-3" style={{'backgroundColor':userSkill.evaluation.color}} >{ userSkill.evaluation.value }</div>
                    </li>
                )}
            </ul>
        </div>
    )
}

