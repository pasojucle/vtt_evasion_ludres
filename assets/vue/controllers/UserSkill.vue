<template>
    <li class="row" v-for="userSkill in store.listFiltered('user_skill')" :key="userSkill.id">
        <div class="col-md-2">{{ userSkill.evaluateAt }}</div>
        <div class="col-md-8" v-html="userSkill.content"></div>
        <div class="col-md-2 text-center p-3" :style="{ 'background-color': userSkill.evaluation.color}" >{{ userSkill.evaluation.value }}</div>
    </li>
</template>

<script>

import { store } from './store.js';


export default {
    props: {
        user: Number,
    },
    data() {
        return {
            edit: null,
            route: null,
            store
        }
    },  
    created() {
        this.store.getList('user_skill', {'user': this.user});
    },
}
</script>