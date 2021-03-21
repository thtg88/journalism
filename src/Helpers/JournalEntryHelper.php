<?php

namespace Thtg88\Journalism\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Thtg88\Journalism\Models\JournalEntry;

/**
 * Helper methods to create journal entries.
 */
class JournalEntryHelper
{
    /**
     * Create a new helper instance.
     *
     * @param bool $auto_register_model_in_morph_map
     * @return void
     */
    public function __construct(
        private $auto_register_model_in_morph_map = false
    ) {
    }

    /**
     * Create a new journal entry instance in storage.
     *
     * @param string                                   $action  The action performing while creating the entry.
     * @param \Illuminate\Database\Eloquent\Model|null $model   The model the action is performed on.
     * @param array|null                               $content The action content data.
     *
     * @return \Thtg88\Journalism\Models\JournalEntry
     */
    public function createJournalEntry(
        string $action,
        ?Model $model = null,
        ?array $content = null,
    ): JournalEntry {
        $target_type = $this->getTargetType($model);
        $id = $model->id ?? null;

        // Build data array to save journal entry
        $data = [
            'target_id'   => $id,
            'target_type' => $target_type,
            'action'      => $action,
        ];

        // Get current authenticated user
        /** @var \Illuminate\Database\Eloquent\Model $user */
        $user = Auth::user();
        if ($user !== null) {
            $user_key = $user->getKeyName();

            $data['user_id'] = $user->$user_key;
        }

        if ($content === null) {
            return JournalEntry::create($data);
        }

        // Remove hidden attributes from being posted in the journals (e.g. password)
        if ($model !== null) {
            $content = array_diff_key(
                $content,
                array_flip($model->getHidden())
            );
        }

        $data['content'] = $content;

        return JournalEntry::create($data);
    }

    public function registerModelInMorphMap(Model $model): string
    {
        $target_type = $model->getTable();

        Relation::morphMap([$target_type => get_class($model)]);

        return $target_type;
    }

    public function getMorphMapEntry(Model $model): ?string
    {
        $target_type = array_search(get_class($model), Relation::morphMap());

        return $target_type === false ? null : $target_type;
    }

    private function getTargetType(?Model $model): ?string
    {
        if ($model === null) {
            return null;
        }

        $target_type = $this->getMorphMapEntry($model);
        if ($target_type !== null) {
            return $target_type;
        }

        // Merge into morph map for access across the app when retrieving
        if ($this->auto_register_model_in_morph_map === true) {
            return $this->registerModelInMorphMap($model);
        }

        return null;
    }
}
