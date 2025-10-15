<?php

namespace A17\Twill\Repositories;

use A17\Twill\Models\Favorite;
use A17\Twill\Repositories\Behaviors\HandleBlocks;

class FavoriteRepository extends ModuleRepository
{
    use HandleBlocks;

    public function __construct(Favorite $model)
    {
        $this->model = $model;
    }
}
