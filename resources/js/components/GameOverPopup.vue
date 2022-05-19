<template>
    <div class="popup">
        <div class="popup-content game-over-popup">
            <h2>Game Over!</h2>
            <div class="players-list">
                <div v-for="(player, index) in game.players" :key="index">{{ player.username }} <span class="points">{{ player.points }}</span></div>
            </div>
            <div v-if="admin" class="mt-large text-center">
                <button class="button button-green" v-on:click.prevent="restart">Restart</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "IntoPopup",
    props: {
        game: {
            type: Object,
            default: null
        },
        admin: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
        };
    },
    methods: {
        restart() {
            this.$emit('restart');
        }
    }
}
</script>

<style scoped>

</style>
