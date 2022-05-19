<template>
    <div class="card" v-bind:class="{ question: isQuestion, 'has-group': group !== null }" @click="$emit('click', card)" v-bind:data-group="group + 1">{{ card.content }}</div>
</template>

<script>
export default {
    name: "Card",
    props: [
        'card', 'group'
    ],
    computed: {
        isQuestion() {
            return this.card.type === 'question';
        }
    }
}
</script>

<style scoped>

</style>
