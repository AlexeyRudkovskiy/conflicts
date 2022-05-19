<?php

namespace App\Models;

use App\Events\GameUpdated;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array $players
 * @property array $played_cards
 * @property array $request_cards
 * @property string $key
 * @property string $id
 * @property int $current_player;
 */
class Game extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [ 'key' ];

    protected $casts = [
        'played_cards' => 'array',
        'players' => 'array',
        'request_cards' => 'array'
    ];

    public function nextPlayer()
    {
        $currentPlayer = $this->current_player;
        $currentPlayer++;
        if ($currentPlayer === count($this->players)) {
            $currentPlayer = 0;
        }

        $this->current_player = $currentPlayer;
        $this->save();

        event(new GameUpdated($this));
    }

}
