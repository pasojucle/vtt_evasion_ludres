import { reactive } from 'vue'
import Routing from 'fos-router';


export const store = reactive({
  list: {},
  filter: {},
  async getList(entity, params = {}) {
    await fetch(Routing.generate(`api_${entity}_list`, params), {
      method: "GET", 
    })
    .then(response => response.json())
    .then(data => {
        this.list[entity] = data.list;
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
    console.log('list', list);
    
    console.log('excluded', excluded);
    if (excluded) {
      console.log('excluded', this.list[excluded]);
      for(let i in list) {
        const result = this.list[excluded].find((itemExcluded) => itemExcluded.id === list[i].id)
        console.log('result', result);
        list[i]['disabled'] = undefined !== result;
      };
    }

    Object.entries(this.filter).forEach(([name, value]) => {
      if (undefined !== value) {
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
    console.log(path);
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
  }
})
