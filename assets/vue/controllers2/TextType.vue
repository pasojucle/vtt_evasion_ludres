<template>
    <input class="form-control form-control-sm" type="text" :name="name" :value="value" :disabled="disabled">
</template>

<script>


export default {
    props: {
        name: String,
        value: String,
        disabled: String,
    },
}
</script>