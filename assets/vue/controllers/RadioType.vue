<template>
    <div className="radio-group-vue" v-for="choice in choices" :key="choice.value">
        <input type="radio" :name="name" :id="choice.id" :value="choice.value" v-model="picked">
        <label className="label" :for="choice.id" :style="{'background-color': getBackground(choice)}">{{ choice.label }}</label>
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
    methods: {
        getBackground(choice) {
            if (this.picked === choice.value) {
                return  choice.color;
            }
            return 'unset';
        }
    },
    created() {
        console.log('choices', this)
        this.picked = this.value;
    },
}
</script>