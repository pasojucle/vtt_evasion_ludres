import { reactive } from 'vue'
import Routing from 'fos-router';
import { checkStatus, isJsonResponse } from './../../js/fetch.js'


export const store = reactive({
  list: {},
  filter: {},
  async getList(entity, params = {}, route = null) {
    if (!route) {
      route = `api_${entity}_list`;
    }
    await fetch(Routing.generate(route, params), {
      method: "GET", 
    })
    .then(response => {console.log('response', route, response); return response.json()})
    .then(json => {
        console.log('json',entity, json)
        this.list[entity] = json.list;
        console.log('list', entity, this.list[entity])
    });
  },
  async edit(entity, params = {}) {
    await fetch(Routing.generate(`api_${entity}_edit`, params), {
      method: "GET", 
    })
    .then(response => response.json())
    .then(data => {
        this.update(data);
        console.log('list', this.list[entity])
    });
  },
  updateAll(data) {
    data.forEach((object) => {
        this.update(object);
    });
  },
  update(data) {
    console.log('update', data)
    if (undefined !== this.list[data.entity]) {
      const index = this.list[data.entity].findIndex(item => {
        return (data.value.id === item.id)
      })
      console.log('index', index, data.value);
      switch(true) {
        case -1 === index:
          console.log('add')
          this.list[data.entity].push(data.value);
          break;
        case data.deleted:
          console.log('delete')
          this.list[data.entity].splice(index, 1);
          break;
        default:
          console.log('update')
          this.list[data.entity].splice(index, 1, data.value)
      }

      this.list[data.entity].sort(this[data.sort]);
    }
    console.log('update list', this.list)
  },
  listFiltered(entity, excluded = null) {
    let list = this.list[entity];    
    if (excluded) {
      excluded = JSON.parse(excluded);
      for(let i in list) {
        const result = this.list[excluded.entity].find((itemExcluded) =>  parseInt(this.resolve(excluded.field, itemExcluded)) === list[i].id)
        list[i]['disabled'] = undefined !== result;
      };
    }

    Object.entries(this.filter).forEach(([name, value]) => {
      if (-1 !== value) {
        list = ('name' === name) 
          ? list.filter(item => this.resolve(name, item) === value)
          : list.filter(item => this.resolve(name, item).id === value);
      }
    })

    return list;
    if (null === this.filter.needle || '' === this.filter.needle) {
      return this.list[entity];
    }

    if (fields) {
        return this.list[entity].filter(item => this.resolve(fields, item) === this.filter.needle);
    }

    return this.list[entity].filter(item => item.name.toLowerCase().includes(this.filter.needle.toLowerCase()));
  },
  resolve(path, obj) {
    return path.split('.').reduce(function(prev, curr) {
        return prev ? prev[curr] : null
    }, obj || self)
  },
  nameASC(a, b) {
    const nameA = a.name.toUpperCase();
    const nameB = b.name.toUpperCase();
  
    return nameA.localeCompare(nameB);
  },
  idASC(a, b) {
    const nameA = a.id;
    const nameB = b.id;
  
    return nameA - nameB;
  },
  toSring(entity) {
    let string;
    switch(true) {
        case undefined !== entity.title:
            string = entity.title;
            break;
        case undefined !== entity.content:
            const htmlElement = document.createRange().createContextualFragment(entity.content);
            string = htmlElement.firstChild.innerText;
            break;
        default:
            string = entity.name;
    }
    return string;
  },
})
