<template>
    <div class="modal" :class="{'fade': edit}" tabindex="-1" role="dialog">           
        <div class="modal-dialog" :class="{ 'modal-open': edit, size: size }" role="document">
            <div v-if="loaded" class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="hide" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ title }}</h4>
                </div>
                <form :action="form.action" @submit.prevent="onSubmit">
                    <div v-html="form.elements" class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" @click="hide">Annuler</button>
                        <button type="submit" class="btn" :class="theme">{{ form.submit }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>

import { store } from './store.js'
import { checkStatus, isJsonResponse } from './../../js/fetch.js'

export default {
    props: {
        edit: Boolean,
        size: String,
        route: String,
    },
    emits: [
        'update:edit'
    ],
    data() {
        return {
            title: null,
            content: null,
            theme: null,
            form: {},
            loaded: false,
            store,
        }
    },

    methods: {
        async onLoad() {           
            await fetch(this.route)
            .then(checkStatus)
            // .then(isJsonResponse)
            .then((response) => response.json())
            .then((json)=> {
                this.form = json.form;
                this.title = json.title;
                this.theme = json.theme;
                this.loaded = true;
                console.log('children', this.form.children)
            });
        },
        async onSubmit(event) {
            const form = event.target;
            Array.from(form.elements).forEach((element) => {
                if ('-1' === element.value) {
                    element.value = null;
                }
            })
            await fetch(form.action, {
                method: 'POST',
                body : new FormData(form),
            })
            .then(checkStatus)
            // .then(isJsonResponse)
            .then((response) => response.json())
            .then((json)=> {
                console.log('response', json)
                if (json.success) {
                    store.update(json.data);
                    this.hide(); 
                }
                if (json.form) {
                    this.form = json.form;
                }
            });
        },
        hide() {
            this.$emit('update:edit', false);
            setTimeout(() => {
                this.loaded = false;
                this.form = {};
            }, 500);
        }
    },
    updated() {
        if (this.edit && this.route && !this.loaded) {
            this.onLoad();
        }
    },
    created() {
        console.log('Edit.vue')
        
    },
}
</script>