<template>
    <div class="container">
        <template v-if="!joined">
            <div class="mb-3">
                <label for="username" class="form-label">Your Username</label>
                <input type="text" class="form-control" id="username" placeholder="Your Username" v-model="username">
            </div>

            <div class="mb-3">
                <label for="gameKey" class="form-label">Join Game</label>
                <input type="text" class="form-control" id="gameKey" placeholder="Game Key" v-model="join_game_key">
            </div>

            <button class="btn btn-primary" @click.prevent="join">Join</button>
            <button class="btn btn-primary" @click.prevent="createGame">Create New Game</button>
        </template>
        <template v-else>
            <div class="alert alert-warning">
                Game Key: {{ join_game_key }}
            </div>
            <h3>Players:</h3>
            <ul>
                <li v-for="player in players">{{ JSON.stringify(player) }}</li>
            </ul>

            <button class="btn btn-success" v-if="!is_game_running" @click.prevent="startGame">Start Game</button>

            <template v-if="is_game_running">
                <template v-if="question !== null">
                    <div class="alert alert-success">
                        <b>Question:</b> {{ question.content }}
                    </div>
                </template>

                <button class="btn btn-primary" @click.prevent="nextQuestion">Next Question</button>
            </template>
        </template>
    </div>
</template>

<script>
    import axios from 'axios'

    export default {
        data() {
            return {
                username: 'Oleksijko',
                join_game_key: '',
                joined: false,

                players: [],
                question: null,
                is_game_running: false
            };
        },

        methods: {

            join() {
                axios.post('/api/join', { key: this.join_game_key, username: this.username })
                    .then(response => response.data)
                    .then(response => this.joined = true);
            },

            createGame() {
                axios.post('/api/create-game')
                    .then(response => response.data)
                    .then(response => response.data)
                    .then(response => {
                        this.join_game_key = response.key;
                        this.join();
                    });
            },

            startGame() {
                axios.post('/api/start', { key: this.join_game_key });
            },

            nextQuestion() {
                axios.post('/api/next-question', { key: this.join_game_key })
                    .then(response => response.data)
                    .then(response => console.log(response))
                    .catch(() => this.is_game_running = false);
            }
        },

        mounted() {
            window.Echo.channel('testing')
                .listen('TestEvent', (e) => {
                    console.log(e);
                })
                .listen('PlayerJoined', (event) => {
                    this.players = event.game.players;
                })
                .listen('NewQuestion', (event) => {
                    this.question = event.card;
                })
                .listen('GameStarted', (event) => {
                    this.is_game_running = true;
                })
            ;
        }
    }
</script>
