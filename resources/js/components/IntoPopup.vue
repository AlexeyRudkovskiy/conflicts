<template>
    <div class="popup">
        <div class="popup-content">
            <h2>Join or create the Game</h2>
            <template v-if="!is_join">
                <input type="text" class="input" placeholder="Enter Username" v-model="username" :disabled="game !== null" />
                <input type="text" class="input mt" placeholder="Game ID" :disabled="gameKey !== null" :value="`Game ID: ${gameKey}`" v-if="game !== null" />
            </template>
            <template v-else>
                <input type="text" class="input" placeholder="Enter Game ID" v-model="game_id" />
            </template>
            <h4 class="mt-large text-center" v-if="game !== null">Players: {{ game.players.length }}</h4>
            <div class="popup-actions" v-if="game === null && !is_join">
                <button class="button button-green" @click.prevent="showJoinInput">Join</button>
                <button class="button button-green" @click.prevent="createGame">Create</button>
            </div>
            <div class="popup-actions" v-if="game === null && is_join">
                <button class="button button-green" @click.prevent="joinGame">Join</button>
            </div>
            <div class="popup-actions" v-if="game !== null && is_create">
                <button class="button button-green" @click.prevent="startTheGame">Start Game</button>
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
        }
    },
    data() {
        return {
            is_join: false,
            is_create: false,
            username: null,
            game_id: null,
            joined: false
        };
    },
    methods: {
        createGame() {
            this.$emit('game:create');
            this.is_create = true;
        },

        showJoinInput() {
            this.is_join = true;
        },

        joinGame() {
            this.$emit('game:join', this.username, this.game_id);
            this.joined = true;
        },

        startTheGame() {
            this.$emit('game:start');
        }
    },
    computed: {
        gameKey() {
            if (this.game === null) {
                return null;
            }

            return this.game.key;
        }
    },
    watch: {
        game(newValue, oldValue) {
            if (typeof oldValue !== "undefined" && this.game !== null && this.is_create && !this.joined) {
                this.$emit('game:join', this.username, this.game.key);
                this.joined = true;
            }
        }
    }
}
</script>

<style scoped>

</style>
