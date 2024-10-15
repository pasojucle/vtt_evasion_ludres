<template>
    <div class="row">
        <SelectFilter class="col-md-6 form-group" className="skill_category" field="category" placeholder="Séléctionner une catégorie"></SelectFilter>
        <SelectFilter class="col-md-6 form-group" className="level" field="level" placeholder="Séléctionner un niveau"></SelectFilter>
    </div>
    <div class="form-group">
        <select lass="form-control form-control-sm" placeholder="filtrer" aria-label="Search">
            <option v-for="entity in store.listFiltered('skill')" :key="entity.id" :value="entity.id">{{ store.toSring(entity) }}</option>
        </select>
    </div>
</template>

<script>

import { store } from './store.js';

import Routing from 'fos-router';
import SelectFilter from './SelectFilter.vue';


export default {

    data() {
        return {
            store
        }
    },  
    components: {
        SelectFilter
    },
    methods: {

    },
    created() {
        this.store.getList('skill_category');
        this.store.getList('level');
        this.store.getList('skill');
    },
}
</script>