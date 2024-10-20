<template>
    <input class="form-control form-control-sm" type="text" :name="name" :value="value">
</template>

<script>


export default {
    props: {
        name: String,
        value: String,
    },
}
</script>