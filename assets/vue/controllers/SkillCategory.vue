<template>
    <a class="btn btn-primary" v-bind:href="path('add')" @click.prevent="handle($event)" title="Ajouter"> Ajouter</a>

    <ul class="list-group">
        <li class="list-group-item" v-for="category in store.listFiltered('skill_category')" :key="category.id">
            <div>{{ category.name }}</div>
            <div class="dropdown">
                <button class="dropdown-toggle" type="button" data-toggle="dropdown-tools"></button>
                <div class="dropdown-menu" data-target="dropdown-tools">
                    <ul class="dropdown-body">
                        <li>
                            <a class="dropdown-item" v-bind:href="path('edit', category)" @click.prevent="handle($event)" title="Modifier"><i class="fas fa-pencil-alt"></i> Modifier</a>
                        </li>
                        <li>
                            <a class="dropdown-item" v-bind:href="path('delete', category)" @click.prevent="handle($event)" title="Supprimer" data-type="danger"><i class="fas fa-times"></i> Supprimer</a>
                        </li>
                    </ul>
                </div>
            </div>
        </li>
    </ul>

    <Edit v-model:edit="edit" :route="route"></Edit>
</template>

<script>

import { store } from './store.js'

import Routing from 'fos-router';
import Edit from './Edit.vue';


export default {
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
        path(action, category = null) {
            const params = (category) ? {'id': category.id} : null;
            return Routing.generate(`api_skill_category_${action}`, params);
        },
        async handle(event) {
            this.route = event.target.href;
            this.edit = true;
        },
    },
    created() {
        this.store.getList('skill_category');
    },
}
</script>