<template>
    <select v-model="selected" class="form-control form-control-sm">
        <option v-for="entity in store.listFiltered(className, exclude)" :key="entity.id" :value="entity.id" :disabled="entity.disabled">{{ store.toSring(entity) }}</option>
    </select>
</template>

<script>

import { store } from './store.js'

export default {
    props: {
        className: String,
        exclude: String,
    },
    data() {
        return {
            store,
            select: undefined,
        }
    },
    created() {
        this.store.getList(this.className);

    },
}
</script>