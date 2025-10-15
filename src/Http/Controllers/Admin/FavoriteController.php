<?php

namespace A17\Twill\Http\Controllers\Admin;

use A17\Twill\Models\Block;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Models\Favorite;
use A17\Twill\Models\Model;
use A17\Twill\Repositories\BlockRepository;
use A17\Twill\Repositories\FavoriteRepository;
use A17\Twill\Services\Forms\Fields\BlockEditor;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Services\Listings\TableColumns;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use A17\Twill\Services\Blocks\Block as BlockConfig;
use Illuminate\Support\Arr;

class FavoriteController extends BaseModuleController
{
    protected $moduleName = 'favorites';
    protected $namespace = 'A17\Twill';

    /**
     * This method can be used to enable/disable defaults. See setUpController in the docs for available options.
     */
    protected function setUpController(): void
    {
        $this->disableCreate();
    }

    /**
     * See the table builder docs for more information. If you remove this method you can use the blade files.
     * When using twill:module:make you can specify --bladeForm to use a blade form instead.
     */
    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->add(
            Input::make()->name('description')->label('Description')
        );
        $form->add(
            BlockEditor::make()
        );

        return $form;
    }

    /**
     * This is an example and can be removed if no modifications are needed to the table.
     */
    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();

        $table->add(
            Text::make()->field('description')->title('Description')
        );

        return $table;
    }

    public function toggleBlockIsFavorite(Request $request, FavoriteRepository $favoriteRepository, BlockRepository $blockRepository, ResponseFactory $responseFactory)
    {
        $favoriteData = $request->get('favorite');
        /**
         * @var Block $block
         */
        $block = $blockRepository->getById($favoriteData['original_block_id']);
        /**
         * @var Favorite $favorite
         */
        $favorite = $block->favorite()->first();
        if ($favorite) {
            $favorite->blocks()->delete();
            $favorite->delete();
            return $responseFactory->json(['success' => true, 'block' => $block], 200);

//            $favoriteBlock->blockable()->delete();
//            $queue = collect([$favoriteBlock]);
//            while ($queue->isNotEmpty()) {
//                $current = $queue->shift();
//
//                foreach ($current->children as $child) {
//                    $queue->push($child);
//                }
//
//                $blockRepository->delete($current->id);
//            }
        } else {
            /**
             * @var $favorite Model
             */
            $favorite = $favoriteRepository->create($favoriteData);
            $block->blockable_id = $favorite->id;
            $block->blockable_type = get_class($favorite);
            $block->parent_id = null;

            $newBlock = $blockRepository->create($block->toArray());
            foreach ($block->medias as $media) {
                $newBlock->medias()->attach($media->id, Arr::only(
                    $media->pivot->toArray(),
                    ['crop_x',
                        'crop_y',
                        'crop_w',
                        'crop_h',
                        'role',
                        'crop',
                        'lqip_data',
                        'ratio',
                        'metadatas',
                        'locale',
                        'position'
                    ]
                ));
            }
            $queue = collect();
            $queue->push([$block, $newBlock->id]);

            while ($queue->isNotEmpty()) {
                [$originalParent, $newParentId] = $queue->shift();

                foreach ($originalParent->children as $child) {
                    $child->blockable_id = $favorite->id;
                    $child->blockable_type = get_class($favorite);
                    $newChild = $blockRepository->create([
                        ...$child->toArray(),
                        'parent_id' => $newParentId
                    ]);
                    foreach ($child->medias as $media) {
                        $newChild->medias()->attach($media->id, Arr::only(
                            $media->pivot->toArray(),
                            ['crop_x',
                                'crop_y',
                                'crop_w',
                                'crop_h',
                                'role',
                                'crop',
                                'lqip_data',
                                'ratio',
                                'metadatas',
                                'locale',
                                'position'
                            ]
                        ));
                    }

                    $queue->push([$child, $newChild->id]);
                }
            }
            return $responseFactory->json(['success' => true, 'block' => $blockRepository->getById($favoriteData['original_block_id'], ['favorite'])], 200);
        }
    }
}
