<template>
    <a class="btn btn-primary" v-bind:href="path('add')" @click.prevent="handle($event)" title="Ajouter"> Ajouter</a>

    <li v-for="userSkill in store.listFiltered(entity)" :key="userSkill.id">
        <form class="row" :action="userSkill.action" @submit.prevent="onSubmit" method="post">
            <div class="col-md-7" v-html="userSkill.content.value"></div>
            <div class="col-md-5">
                <RadioType :name="userSkill.evaluation.name" :value="userSkill.evaluation.value" :choices="userSkill.evaluation.choices" @update:submit="submit"></RadioType>
            </div>
            <HiddenType :name="userSkill.skill.name" :value="userSkill.skill.value"></HiddenType>
            <HiddenType :name="userSkill.user.name" :value="userSkill.user.value"></HiddenType>
            <HiddenType :name="userSkill._token.name" :value="userSkill._token.value"></HiddenType>
        </form>
    </li>

    <Edit v-model:edit="edit" :route="route"></Edit>
</template>

<script>

import Routing from 'fos-router';
import { store } from './store.js';
import Edit from './Edit.vue';
import RadioType from './RadioType.vue';
import HiddenType from './HiddenType.vue';
import { checkStatus, isJsonResponse } from './../../js/fetch.js'


export default {
    props: {
        user: Number,
    },
    components: {
        RadioType,
        HiddenType,
        Edit,
    },
    data() {
        return {
            entity: 'user_skill_edit',
            action: null,
            token: {'name' : null, 'value': null},
            store,
            edit: null,
            route: null,
        }
    }, 
    methods: {
        async getList() {
            await fetch(Routing.generate('api_user_skill_list_edit', {'user': this.user}), {
                method: "GET", 
            })
            .then(response => response.json())
            .then(data => {
                this.store.list[this.entity] = data.list;
            });
        },
        getProps(components, componentName, propName = null) {
            if (!components) {
                return null;
            }
            const component = components.find((component) => component.field === componentName);
            if (propName && component) {
                return component.props[propName];
            }
            return component.props
        },
        async submit(element) {
            const form = element.closest('form');

            console.log('submit', element)
            await fetch(form.action, {
                method: 'POST',
                body : new FormData(form),
            })
            .then(checkStatus)
            // .then(isJsonResponse)
            .then((response) => response.json())
            .then((json)=> {
                console.log('response', json)
            });
        },
        path(action) {
            const params = (this.user) ? {'user': this.user} : null;
            return Routing.generate(`api_user_skill_${action}`, params);
        },
        async handle(event) {
            this.route = event.target.href;
            this.edit = true;
        },
    },
    created() {
        this.getList();
    },
}
</script>