import React, { useState, useEffect } from 'react';
import { getDataFromApi, resolve } from '../utils'
import EntityAutocompleteFilter from '../components/EntityAutocompleteFilter';
import ChoiceAutocompleteFilter from '../components/ChoiceAutocompleteFilter';
import Paginator from '../components/Paginator';
import TextRaw from '../components/TextRaw.jsx';
import Dropdown from '../components/Dropdown.jsx';
import Settings from '../components/Settings.jsx';
import Actions from '../components/Actions.jsx';
import Edit from '../components/Edit';
import Routing from 'fos-router';

export default function UserList({api}) {

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

    const [editAction, setEditAction] = useState(false);
    const [routeAction, setRouteAction] = useState(null);

    useEffect(() => {
        getDataFromApi(api.levels)
            .then((data) => {
                console.log('levels', data)
                setLevelList(data);
        })
        getDataFromApi(api.permissions)
            .then((data) => {
                console.log('permissions', data)
                setPermissionList(data);
        })
        getDataFromApi(api.seasons)
            .then((data) => {
                console.log('season', data)
                setSeasonList(data);
                const currentSeason = data.find(season => season.id!==undefined)
                setSeason(currentSeason);
        })
        getDataFromApi(api.users)
            .then((data) => {
                setUserList(data);
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

    const handleEditAction = (value) => {
        setEditAction(value)
    }

    const handleRouteAction = (value) => {
        setEditAction(true);
        setRouteAction(value);
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

        return list.filter((user) => permissions.some((permission) => {
            return user.permissions.some((userPermission) => userPermission.id === permission.id)
        }))
    }

    const listFiltered = (save) => {
        let list = userFilter(userList);
        list = seasonFilter(list)
        list = permissionFilter(list)
        list = levelFilter(list);
        if (save) {
            saveListFiltered(list);
        }
        return list;
    }

    const saveListFiltered = async(list) => {
        if (isLoad) {
            const data = new FormData();
            data.append('list[name]', 'admin_users_list');
            // data.append('list[values]', JSON.stringify(list));
            // data.append('list[filters][user]', JSON.stringify(user));
            // data.append('list[filters][levels]', JSON.stringify(levels));
            // data.append('list[filters][permissions]', JSON.stringify(permissions));
            // data.append('list[filters][season]', JSON.stringify(season));
            data.append('list[values]', JSON.stringify(list.map(x=>x.id)));

            await fetch(Routing.generate('admin_save_list_filtered'), {
                method: 'POST',
                body : data,
            })
            .then((response) => response.json())
            .then((json)=> {
                console.log('saveListFiltered', json);
            });
        }
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
                            <Dropdown title={user.actions.title} actions={user.actions.items} id={user.id}/>
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
        <>
            <div className="wrapper-title">
                <h1>Gestion des adhérents - <span className="badge badge-info">{listFiltered(false).length}</span></h1>
                <div className="tool-group">
                    <a href="{{ path('wiki_show', {'directory': 'adhérents'})}}" target="_blank" title="wiki" className="btn-wiki"></a>
                    <Settings api={api} handleRouteAction={handleRouteAction} />
                    <Actions api={api} handleRouteAction={handleRouteAction} />
                </div>
            </div>
            <div className="wrapper-body">
                <div className="row">
                    <ChoiceAutocompleteFilter list={userListFiltered()} value={user} label={null} placeholder="Selectionner un adhérent" handleAdd={handleAddUser} handleRemove={handleRemoveUser} className="col-md-6 form-group"/>
                    <ChoiceAutocompleteFilter list={seasonList} value={season} label={null} placeholder="Toutes les saisons" handleAdd={handleAddSeason} handleRemove={handleRemoveSeason}  className="col-md-6 form-group"/>
                    <ChoiceAutocompleteFilter list={permissionList} value={permissions} label={null} placeholder="Toutes les permissions" handleAdd={handleAddPermission} handleRemove={handleRemovePermission}  className="col-md-12 form-group"/>
                    <ChoiceAutocompleteFilter list={levelList} value={levels} label={null} placeholder="Toutes les niveaux" handleAdd={handleAddLevel} handleRemove={handleRemoveLevel}  className="col-md-12 form-group"/>
                </div>
                <List/>
                <Paginator list={listFiltered(true)} page={page} maxResults={maxResults} handlePageChange={handlePageChange} handleMaxResultsChange={handleMaxResultsChange}/>
            </div>
            <Edit edit={editAction} route={routeAction} size="lg" handleEditChange={handleEditAction}/>
        </>
    )
}

