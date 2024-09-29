<template>
    <div class="form-group">
        <select v-model="store.filter.needle" class="form-control form-control-sm" placeholder="filtrer" aria-label="Search">
            <option value="" selected>Tous</option>
            <option v-for="entity in store.list[this.className]" :key="entity.id" :value="entity.id">{{ entity.name }}</option>
        </select>
    </div>
</template>

<script>

import { store } from './store.js'

export default {
    props: {
        className: String,
    },
    data() {
        return {
            store,
        }
    },
    created() {
        this.store.getList(this.className);
    },
}
</script>