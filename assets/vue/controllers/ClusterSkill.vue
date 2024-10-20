<template>
    <a class="btn btn-primary" v-bind:href="path('add')" @click.prevent="handle($event)" title="Ajouter"> Ajouter</a>

    <li class="list-group-item" v-for="skill in store.list['cluster_skill']" :key="skill.id">
        <div v-html="skill.content"></div>
        <div class="dropdown">
            <button class="dropdown-toggle" type="button" data-toggle="dropdown-tools"></button>
            <div class="dropdown-menu" data-target="dropdown-tools">
                <ul class="dropdown-body">
                    <li><a class="dropdown-item" v-bind:href="path('delete', skill)" @click.prevent="handle($event)" title="Supprimer" data-type="danger"><i class="fas fa-times"></i> Supprimer</a></li>
                    <li><a href="" class="dropdown-item" title="Évaluations"><i class="fa-solid fa-graduation-cap"></i> Évaluer</a></li>
                </ul>
            </div>
        </div>
    </li>
    <Edit v-model:edit="edit" :route="route"></Edit>
</template>

<script>

import { store } from './store.js';

import Routing from 'fos-router';
import Edit from './Edit.vue';


export default {
    props: {
        cluster: Number,
    },
    data() {
        return {
            edit: null,
            route: null,
            store
        }
    },  
    components: {
        Edit
    },
    methods: {
        path(action, skill = null) {
            const params = {'cluster': this.cluster};
            if (skill) {
                params['skill'] = skill.id;
            }
            return Routing.generate(`api_cluster_skill_${action}`, params);
        },
        async handle(event) {
            this.route = event.target.href;
            this.edit = true;
        },
    },
    created() {
        this.store.getList('cluster_skill', {'cluster': this.cluster});
        console.log
    },
}
</script>