<template>
    <div class="row">
        <SelectFilter class="col-md-6 form-group" className="skill_category" field="category" placeholder="Séléctionner une catégorie"></SelectFilter>
        <SelectFilter class="col-md-6 form-group" className="level" field="level" placeholder="Séléctionner un niveau"></SelectFilter>
    </div>
    <div class="form-group">
        <select lass="form-control form-control-sm" aria-label="Search" name="form[skill]">
            <option v-for="entity in store.listFiltered('skill', 'cluster_skill')" :key="entity.id" :value="entity.id" :disabled="entity.disabled">{{ store.toSring(entity) }}</option>
        </select>
    </div>
</template>

<script>

import { store } from './store.js';

import SelectFilter from './SelectFilter.vue';

import ChoiceFilterType from './ChoiceFilterType.vue';

import SelectFilteredType from './ChoiceFilteredType.vue';


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
        // this.store.getList('skill_category');
        // this.store.getList('level');
        this.store.getList('skill');
    },
}
</script>