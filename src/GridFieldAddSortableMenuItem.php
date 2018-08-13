<?php

namespace Symbiote\SortableMenu;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataList;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;

/**
 * Like 'GridFieldAddExistingAutocompleter' but instead updates a boolean value of a record when added.
 */
class GridFieldAddSortableMenuItem extends GridFieldAddExistingAutocompleter
{
    protected $fieldName = '';

    protected $sortFieldName = '';

    public function __construct($fieldName, $sortFieldName = null, $targetFragment = 'before', $searchFields = null)
    {
        parent::__construct($targetFragment, $searchFields);
        $this->fieldName = $fieldName;
        $this->sortFieldName = $sortFieldName;
        if (!$this->sortFieldName) {
            $this->sortFieldName = 'Sort'.$this->fieldName;
        }
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        switch ($actionName) {
            case 'addto':
                if (isset($data['relationID']) && $data['relationID']) {
                    $gridField->State->GridFieldAddRelation = $data['relationID'];
                    $class = $gridField->getList()->dataClass();
                    $record = DataList::create($class)->filter(array(
                        'ID' => $data['relationID'],
                    ))->first();
                    if ($record) {
                        if (!$record->{$this->fieldName}) {
                            if (!$record->canEdit()) {
                                return;
                            }
                            $record->{$this->fieldName} = 1;
                            if ($record->{$this->sortFieldName} == 0) {
                                $maxSort = DataList::create($class)->filter(array(
                                    $this->fieldName => 1,
                                ))->max($this->sortFieldName) + 1;
                                $record->{$this->sortFieldName} = $maxSort;
                            }
                            $record->write();
                        }
                    }
                }
                break;
        }
    }
}
