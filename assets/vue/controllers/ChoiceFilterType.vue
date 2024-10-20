<template>
    <select v-model="store.filter[field]" class="form-control form-control-sm" placeholder="filtrer" aria-label="Search">
        <!-- <option :value="undefined" selected>{{ placeholder }}</option> -->
        <option v-for="entity in store.list[this.className]" :key="entity.id" :value="entity.id">{{ store.toSring(entity) }}</option>
    </select>
</template>

<script>

import { store } from './store.js'

export default {
    props: {
        className: String,
        field: String,
        placeholder: String,
    },
    data() {
        return {
            store,
        }
    },
    methods: {
        
    },
    created() {
        this.store.getList(this.className);
        this.store.filter[this.field] = undefined;
    },
}
</script>