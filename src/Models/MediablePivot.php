<?php

namespace A17\Twill\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class MediablePivot extends MorphPivot
{
    protected $casts = [
        'metadatas' => 'array',
    ];
}
