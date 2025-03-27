import React, { useState, useEffect } from 'react';
import { getData, resolve } from '../utils'
import EntityAutocompleteFilter from '../components/EntityAutocompleteFilter';
import ChoiceAutocompleteFilter from '../components/ChoiceAutocompleteFilter';
import Paginator from '../components/Paginator';
import TextRaw from '../components/TextRaw.jsx';
import Dropdown from '../components/Dropdown.jsx'

export default function UserList() {

    const [isLoad, setIsLoad] = useState(false);
    const [user, setUser] = useState(null);
    const [clearUser, setClearUser] = useState(false);
    const [level, setLevel] = useState(null);
    const [clearLevel, setClearLevel] = useState(false);
    const [levelList, setLevelList] = useState([])
    const [userList, setUserList] = useState([]);
    const [season, setSeason] = useState(null);
    const [clearSeason, setClearSeason] = useState(false);
    const [seasonList, setSeasonList] = useState([]);
    const [page, setPage] = useState(1);
    const [maxResults, setMaxResults] = useState(10);

    useEffect(() => {
        const list = getData('api_user_list')
            .then((data) => {
                setUserList(data.list);
                setSeasonList(data.seasons);
                const currentSeason = data.seasons.find(season => season.id!==undefined)
                setSeason(currentSeason);
                setLevelList(data.levels);
                setIsLoad(true);
            })
    }, [])

    const handleChangeUser = (value) => {
        setUser(value)
    }

    const handleChangeClearUser = (value) => {
        setClearUser(value)
    }

    const handleChangeLevel = (value) => {
        setLevel(value)
    }

    const handleChangeClearLevel = (value) => {
        setClearLevel(value)
    }

    const handleChangeSeason = (value) => {
        setSeason(value)
    }

    const handleChangeClearSeason = (value) => {
        setClearSeason(value)
    }

    const userFilter = (list) => {
        
        if (!user) {
            return list;
        }
        return list.filter((item) => item.id === user.id)
    }

    const levelFilter = (list) => {
        if (!level) {
            return list;
        }
        return list.filter((item) => {
            if (level.target) {
                return resolve(level.target, item) === level.value;
            }
            return item.level.id === level.id
        })
    }

    const seasonFilter = (list) => {
        if (!season) {
            return list;
        }
        return list.filter((item) => item.seasons.includes(season))
    }

    const listFiltered = () => {
        let list = userFilter(userList);
        list = seasonFilter(list)
        return levelFilter(list);
    }

    const pagnedListFiltered = () => {
        let list = userFilter(userList);
        list = seasonFilter(list);
        list = levelFilter(list);
        const end = page * maxResults 
        console.log('paginator', (end - maxResults), end)
        return list.slice((end - maxResults), end)
    }


    const userListFiltered = () => {
        const list = seasonFilter(userList)
        return levelFilter(list);
    }

    const handlePageChange = (value) => {
        setPage(value)
    }

    const handleMaxResultsChange = (value) => {
        setMaxResults(value)
        setPage(1)
    }

    const List = () => {
        if (isLoad) {
            return (
                <ul className='list-group'>
                    {pagnedListFiltered().map((user) => 
                        <li className="list-dropdown" key={user.id}> 
                            <a href={ user.btnShow }
                                style={{backgroundColor: user.level.color }}>
                                <div className="row">
                                    <div className="col-md-4 col-xs-12">{ user.fullName }</div>
                                    <div className="col-md-4 col-xs-12" >{ user.testingBikeRides }</div>
                                    <div className="col-md-3 col-xs-10">{ user.level.name }</div>
                                    <TextRaw textHtml={user.boardMember} className="col-md-1 col-xs-2"/>
                                </div>
                            </a> 
                            <Dropdown title={user.actions.title} actions={user.actions.items}/>
                        </li>
                    )}
                </ul>
            )
        }

        return(
            <div className="loader-container">
                <div className="loader"></div>
            </div>
        )
    }

    return (
        <div>
            <div className="row">
                <ChoiceAutocompleteFilter list={userListFiltered()} value={user} label="Adhérent" placeholder="Selectionner un adhérent" handleChange={handleChangeUser} isClear={clearUser} handleClear={handleChangeClearUser} className="col-md-6 form-group"/>
                <ChoiceAutocompleteFilter list={seasonList} value={season} label="Saison" placeholder="Toutes les saisons" handleChange={handleChangeSeason} isClear={clearSeason} handleClear={handleChangeClearSeason}  className="col-md-6 form-group"/>
                <ChoiceAutocompleteFilter list={levelList} value={level} label={null} placeholder="Toutes les niveaux" handleChange={handleChangeLevel} isClear={clearLevel} handleClear={handleChangeClearLevel}  className="col-md-12 form-group"/>
            </div>
            <List/>
            <Paginator list={listFiltered()} page={page} maxResults={maxResults} handlePageChange={handlePageChange} handleMaxResultsChange={handleMaxResultsChange}/>
        </div>
    )
}

