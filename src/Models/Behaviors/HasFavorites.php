<?php

namespace A17\Twill\Models\Behaviors;

use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasFavorites
{
    public function favorite(): HasOne
    {
        return $this->hasOne(twillModel('favorite'), 'original_block_id');
    }
}
