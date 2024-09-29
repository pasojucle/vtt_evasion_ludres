import { reactive } from 'vue'
import Routing from 'fos-router';


export const store = reactive({
  list: {
    'skill': [],
    'skillCategory': [],
  },
  filter: {
    needle: null,
    checked: false
  },
  async getList(entity, params = {}) {
    await fetch(Routing.generate(`api_${entity}_list`, params), {
      method: "GET", 
    })
    .then(response => response.json())
    .then(data => {
        this.list[entity] = data.list;
        console.log('list', this.list[entity])
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
  update(object) {
    if (undefined !== this.list[object.entity]) {
      const index = this.list[object.entity].findIndex(item => {
        return (object.value.id === item.id)
      })
      if (-1 !== index) {
        this.updateList(object.value, object.entity, index);
        this.list[object.entity].sort(this[object.sort]);
        return;
      }
      this.list[object.entity].push(object.value);
      this.list[object.entity].sort(this[object.sort]);
    }
    console.log('update list', this.list)
  },
  updateList(data, entity, index) {
    if (-1 < index) {
      this.list[entity].splice(index, 1, data);
      return;
    }
    this.list[entity].push(data);
  },
  listFindById(entity, entityId) {
    console.log('listFindById', entityId, this.list[entity])
    return this.list[entity].find(({id}) => id === parseInt(entityId));
  },
  listFiltered(entity, fields = null) {
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
}
})
