<template>
    <label for="id">{{ label }}</label>
    <select v-model="selected" class="form-control form-control-sm" :name="name" :id="id">
        <option v-for="entity in store.list[this.className]" :key="entity.id" :value="entity.id">{{ store.toSring(entity) }}</option>
    </select>
</template>

<script>

import { store } from './store.js'

export default {
    props: {
        id: String,
        name: String,
        className: String,
        exclude: String,
        value: String,
        label: String,
    },
    data() {
        return {
            store,
        }
    },
    created() {
        this.store.getList(this.className);
        this.selected = this.value;
    },
}
</script>