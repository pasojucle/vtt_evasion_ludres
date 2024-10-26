<template>
<div class="form-group">
    <p>{{ picked }}</p>
    <div className="radio-group-vue" v-for="choice in choices" :key="choice.value">
        <input type="radio" :name="name" :id="choice.id" :value="choice.value" v-model="picked">
        <label className="label" :for="choice.id">{{ choice.label }}</label>
    </div>
</div>
</template>


<script>

export default {
    props: {
        name: String,
        value: String,
        choices: Array,
    },
    data() {
        return {
            picked: null,
        }
    },  
    created() {
        console.log('choices', this)
        this.selected = this.value;
    },
}
</script>