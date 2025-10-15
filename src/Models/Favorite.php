<?php

namespace A17\Twill\Models;

use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasPosition;
use A17\Twill\Models\Behaviors\Sortable;
use A17\Twill\Models\Contracts\TwillModelContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Favorite extends Model implements Sortable, TwillModelContract
{
    use HasBlocks;
    use HasPosition;

    protected $fillable = [
        'published',
        'title',
        'description',
        'position',
        'original_block_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeAccessible(Builder $query): Builder
    {
        // Usually meant for permission checks. For now, just return as-is.
        return $query;
    }

    public function scopeOnlyTrashed(Builder $query): Builder
    {
        return $query->onlyTrashed();
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('published', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Twill Translations
    |--------------------------------------------------------------------------
    */

    public function getTranslatedAttributes(): array
    {
        // If you want translations (requires HasTranslation trait),
        // return the attributes that should be translated
        return [
            'title',
            'description',
        ];
    }

    public function originalFavoriteBlock()
    {
        return $this->belongsTo(Block::class, 'original_block_id');
    }
}
