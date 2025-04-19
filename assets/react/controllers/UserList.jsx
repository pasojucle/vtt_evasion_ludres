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
    const [userList, setUserList] = useState([]);

    const [levels, setLevels] = useState([]);
    const [levelList, setLevelList] = useState([]);

    const [permissions, setPermissions] = useState([]);
    const [permissionList, setPermissionList] = useState([]);

    const [season, setSeason] = useState(null);
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
                setPermissionList(data.permissions)
                setIsLoad(true);
            })
    }, [])

    const handleAddUser = (value) => {
        setUser(value)
    }

    const handleRemoveUser = () => {
        setUser(null)
    }

    const handleAddLevel = (level) => {
        setLevels([
            ...levels,
            level
        ])
    }

    const handleRemoveLevel = (level) => {
        setLevels(               
            levels.filter(a =>
                a.id !== level.id
            )
        );
    }

    const handleAddPermission = (permission) => {
        setPermissions([
            ...permissions,
            permission
        ])
    }

    const handleRemovePermission = (permission) => {
        setPermissions(
            permissions.filter(p => p.id !== permission.id)
        )
    }

    const handleAddSeason = (value) => {
        setSeason(value)
    }

    const handleRemoveSeason = () => {
        setSeason(null)
    }

    const userFilter = (list) => {
        if (!user) {
            return list;
        }
        return list.filter((item) => item.id === user.id)
    }

    const levelFilter = (list) => {
        if (0 === levels.length) {
            return list;
        }
        return list.filter((item) => {
            return levels.some((level) => {
                if (level.target) {
                    return resolve(level.target, item) === level.value;
                }
                return levels.some((level) => item.level.id === level.id);
            })
        })
    }

    const seasonFilter = (list) => {
        if (!season) {
            return list;
        }
        return list.filter((item) => item.seasons.includes(season.id))
    }

    const permissionFilter = (list) => {
        if (0 === permissions.length) {
            return list;
        }
        return list.filter((item) => permissions.some((permission) => item.permissions.includes(permission.id)))
    }

    const listFiltered = () => {
        let list = userFilter(userList);
        list = seasonFilter(list)
        list = permissionFilter(list)
        return levelFilter(list);
    }

    const pagnedListFiltered = () => {
        let list = userFilter(userList);
        list = seasonFilter(list);
        list = permissionFilter(list)
        list = levelFilter(list);
        const end = page * maxResults
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
                <ChoiceAutocompleteFilter list={userListFiltered()} value={user} label={null} placeholder="Selectionner un adhérent" handleAdd={handleAddUser} handleRemove={handleRemoveUser} className="col-md-6 form-group"/>
                <ChoiceAutocompleteFilter list={seasonList} value={season} label={null} placeholder="Toutes les saisons" handleAdd={handleAddSeason} handleRemove={handleRemoveSeason}  className="col-md-6 form-group"/>
                <ChoiceAutocompleteFilter list={permissionList} value={permissions} label={null} placeholder="Toutes les permissions" handleAdd={handleAddPermission} handleRemove={handleRemovePermission}  className="col-md-12 form-group"/>
                <ChoiceAutocompleteFilter list={levelList} value={levels} label={null} placeholder="Toutes les niveaux" handleAdd={handleAddLevel} handleRemove={handleRemoveLevel}  className="col-md-12 form-group"/>
            </div>
            <List/>
            <Paginator list={listFiltered()} page={page} maxResults={maxResults} handlePageChange={handlePageChange} handleMaxResultsChange={handleMaxResultsChange}/>
        </div>
    )
}

