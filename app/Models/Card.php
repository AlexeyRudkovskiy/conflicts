<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property string $content
 * @property string $type question|answer
 * @property int $answers
 */
class Card extends Model
{
    use HasFactory;

    public const TYPE_QUESTION = 'question';
    public const TYPE_ANSWER = 'answer';
    public const TYPES = [ self::TYPE_QUESTION, self::TYPE_ANSWER ];

    public const CARDS_PER_PLAYER = 10;

    protected $fillable = [
        'content', 'type', 'answers'
    ];

    public function scopeQuestions(Builder $builder)
    {
        return $builder->where('type', self::TYPE_QUESTION);
    }

    public function scopeAnswers(Builder $builder)
    {
        return $builder->where('type', self::TYPE_ANSWER);
    }

    public function scopeNotPlayed(Builder $builder, array $ids)
    {
        return $builder->whereNotIn('id', $ids);
    }

}
