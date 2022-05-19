<?php

namespace App\Http\Controllers;

use App\Events\GameFinished;
use App\Events\GameStarted;
use App\Events\GameUpdated;
use App\Events\NewAnswer;
use App\Events\NewQuestion;
use App\Events\PlayerJoined;
use App\Events\PushCard;
use App\Events\RestartGame;
use App\Events\TestEvent;
use App\Http\Resources\GameResponse;
use App\Models\Card;
use App\Models\Game;
use Illuminate\Http\Request;

class ApiController extends Controller
{

    public function createGame()
    {
        $game = Game::factory()->create();

        return new GameResponse($game);
    }

    public function joinGame(Request $request)
    {
        $key = $request->get('key', null);
        $username = $request->get('username', null);

        $request->session()->put('@username', $username);

        /** @var Game $game */
        $game = Game::query()->where('key', $key)->first();
        $players = $game->players;
        array_push($players, [
            'username' => $username,
            'points' => 0,
            'played_cards' => []
        ]);
        $game->players = $players;
        $game->save();

        event(new PlayerJoined($game));

        return new GameResponse($game);
    }

    public function startGame(Request $request)
    {
        $key = $request->get('key', null);
        /** @var Game $game */
        $game = Game::query()->where('key', $key)->first();
        $game->current_player = -1;

        /// Give cards
        $takenCards = collect($game->players)
            ->reduce(function ($acc, $player) {
                $cards = Card::query()
                    ->answers()
                    ->whereNotIn('id', $acc)
                    ->inRandomOrder()
                    ->take(Card::CARDS_PER_PLAYER)
                    ->get();

                return $cards
                    ->each(fn (Card $card) => event(new PushCard($player['username'], $card)))
                    ->map(fn (Card $card) => $card->id)->merge($acc);
            }, []);

        $game->played_cards = $takenCards;
        $game->save();

        event(new GameStarted($game));
    }

    public function nextQuestion(Request $request)
    {
        $key = $request->get('key', null);
        /** @var Game $game */
        $game = Game::where('key', $key)->first();

        $card = Card::query()
            ->questions()
            ->notPlayed($game->played_cards)
            ->inRandomOrder()
            ->first();

        if ($card !== null) {
            $playedCards = $game->played_cards;;
            array_push($playedCards, $card->id);

            $game->played_cards = $playedCards;
            $game->save();

            event(new NewQuestion($card));

            $game->nextPlayer();

            return response()->json($card);
        } else {
            event(new GameFinished());

            return response()->json([
                'response' => 'error',
                'message' => 'All questions already played!'
            ])->setStatusCode(500);
        }
    }

    public function placeAnswer(Request $request)
    {
        $key = $request->get('key', null);
        /** @var Game $game */
        $game = Game::where('key', $key)->first();
        $cardId = $request->get('answer_id', null);

        $card = Card::query()
            ->answers()
            ->where('id', $cardId)
            ->first();

        $playedCards = $game->played_cards;
        $players = $game->players;
        $playedCards[] = $card->id;
        $myUsername = session()->get('@username');

        foreach ($players as $index => $player) {
            if ($player['username'] === $myUsername) {
                $player['played_cards'][] = $card->id;

                $players[$index] = $player;
            }
        }

        $game->players = $players;
        $game->played_cards = $playedCards;
        $game->save();

        $userCode = substr(sha1($myUsername), 0, 5);

        event(new NewAnswer($card, $userCode));

        return response()->json([])->setStatusCode(201);
    }

    public function selectAnswer(Request $request)
    {
        $key = $request->get('key', null);
        /** @var Game $game */
        $game = Game::where('key', $key)->first();
        $playedCards = $game->played_cards;
        $players = $game->players;
        $cardId = $request->get('answer_id', null);

        $winnerCard = Card::query()
            ->answers()
            ->where('id', $cardId)
            ->first();

        foreach ($players as $index => $player) {
            if (in_array($winnerCard->id, $player['played_cards'])) {
                $player['points']++;
            }

            $newCardsCount = count($player['played_cards']);
            $cards = Card::query()
                ->answers()
                ->whereNotIn('id', $playedCards)
                ->inRandomOrder()
                ->take($newCardsCount)
                ->get();

            $ids = $cards->pluck('id');
            $player['played_cards'] = [];
            $players[$index] = $player;

            foreach ($cards as $card) {
                event(new PushCard($player['username'], $card));
            }

            array_push($playedCards, ...$ids);
        }

        $game->played_cards = $playedCards;
        $game->players = $players;
        $game->save();

        event(new GameUpdated($game));
    }

    public function restart(Request $request)
    {
        $key = $request->get('key', null);
        /** @var Game $game */
        $game = Game::where('key', $key)->first();

        $players = $game->players;
        $game->played_cards = [];
        $game->current_player = 0;

        foreach ($players as $index => $player) {
            $player['points'] = 0;
            $player['played_cards'] = [];

            $players[$index] = $player;
        }

        $game->players = $players;
        $game->save();

        event(new RestartGame($game));
    }

}
