<template>
<div class="game-area">
    <div class="sidebar">
        <div class="current">
            <template v-if="current_question !== null && is_game_running">
                <card :card="current_question" v-if="current_question !== null" :group="null" />
                <div class="current-player">{{ current_player.username }}</div>
            </template>
        </div>
        <div class="players-list">
            <h3>Players</h3>
            <ul>
                <template v-if="current_game !== null">
                    <li v-for="(player, index) in current_game.players"
                        :key="player.username"
                        :class="{ 'me': player.username === my_username, active: index === current_game.current_player }"
                        :data-points="player.points"
                    >{{ player.username }}</li>
                </template>
            </ul>
        </div>
    </div>
    <div class="content">
        <div class="answers">
            <template v-if="current_question !== null && current_question.answers > 1">
                <template v-for="(_cards, index) in groupedCards">
                    <card v-for="card in _cards" :card="card" :key="card.id" :group="index" @click="selectAnswer" />
                </template>
            </template>
            <template v-else>
                <card v-for="card in cards" :card="card" :key="card.id" :group="null" @click="selectAnswer" />
            </template>
        </div>
        <div class="my-cards">
            <card v-for="card in my_cards" :card="card" :key="card.id" :group="null" @click="placeAnswer" />
        </div>
    </div>

    <into-popup v-if="!is_game_running"
                @game:create="createGame"
                @game:join="joinGame"
                @game:start="startGame"
                :game="current_game" />
    <game-over-popup v-if="is_finished" :game="current_game" :admin="is_admin" @restart="restartTheGame" />
</div>
</template>

<script>
import Card from './Card'
import IntoPopup from './IntoPopup'
import GameOverPopup from './GameOverPopup'
import axios from 'axios'

export default {
    name: "GameArea",
    components: { Card, IntoPopup, GameOverPopup },
    data() {
        return {
            is_game_started: false,
            is_game_running: false,
            is_finished: false,
            is_joined: false,
            current_game: null,
            current_question: null,
            my_username: null,
            my_answers: 0,
            is_admin: false,

            cards: [
            ],
            my_cards: [
            ]
        };
    },
    methods: {
        createGame() {
            axios.post('/api/create-game')
                .then(response => response.data)
                .then(response => response.data)
                .then(response => {
                    this.current_game = response;
                    this.is_admin = true;
                });
        },

        joinGame(username, key) {
            this.my_username = username;

            axios.post('/api/join', { key, username })
                .then(response => response.data)
                .then(response => response.data)
                .then(response => {
                    this.joined = true;
                    this.current_game = response;
                });
        },

        startGame() {
            axios.post('/api/start', { key: this.current_game.key })
                .then(() => this.nextQuestion());
        },

        nextQuestion() {
            axios.post('/api/next-question', { key: this.current_game.key })
                .then(response => response.data)
                .then(response => this.current_question = response)
                .catch(() => {
                    this.is_game_running = false;
                });
        },

        placeAnswer(card) {
            if (this.my_answers >= this.current_question.answers) {
                return ;
            }

            /// todo: uncomment on live
            if (this.current_player.username === this.my_username) {
                return ;
            }

            axios.post(`/api/place-answer`, { key: this.current_game.key, answer_id: card.id })
                .then(response => this.removeCard(card.id))
                .then(() => this.my_answers++);
        },

        selectAnswer(card) {
            axios.post(`/api/select-answer`, { key: this.current_game.key, answer_id: card.id })
                .then(() => this.my_answers = 0)
                .then(() => this.nextQuestion());
        },

        removeCard(id) {
            this.my_cards = this.my_cards.filter(card => card.id !== id);
        },

        restartTheGame() {
            axios.post(`/api/restart`, { key: this.current_game.key });
        }
    },

    computed: {
        current_player() {
            if (!this.is_game_running) {
                return { points: 0, username: '' };
            }

            return this.current_game.players[this.current_game.current_player];
        },

        groupedCards() {
            const groups = this.cards.reduce((acc, item) => {
                if (typeof acc[item.user] === "undefined") {
                    acc[item.user] = [];
                }
                acc[item.user].push(item);

                return acc;
            }, {});

            return Object.values(groups);
        }
    },

    mounted() {
        window.Echo.channel('game')
            .listen('PlayerJoined', (event) => {
                this.current_game.players = event.game.players;
                this.current_game.current_player = 0;
            })
            .listen('GameStarted', (event) => {
                this.is_game_started = true;
                this.is_game_running = true;
            })
            .listen('PushCard', (event) => {
                if (event.player === this.my_username) {
                    this.my_cards.push(event.card);
                }
            })
            .listen('NewQuestion', (event) => {
                const {card} = event;
                this.current_question = card;
                this.my_answers = 0;
                this.cards = [];
            })
            .listen('NewAnswer', (event) => {
                const {card} = event;
                card.user = event.userCode;
                this.cards.push(card);
            })
            .listen('GameUpdated', (event) => {
                const {game} = event;
                this.current_game.current_player = game.current_player;
                this.current_game.players = game.players;
            })
            .listen('RestartGame', (event) => {
                const {game} = event;
                this.current_game.current_player = game.current_player;
                this.current_game.players = game.players;

                this.is_finished = false;
                this.is_game_running = true;
                this.cards = [];
                this.my_cards = [];

                if (this.is_admin) {
                    window.setTimeout(() => this.startGame(), 250);
                }
            })
            .listen('GameFinished', () => {
                this.is_finished = true;
                this.is_game_running = false;
            })
        ;
    }
}
</script>

<style scoped>

</style>
