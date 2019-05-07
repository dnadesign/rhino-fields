<?php

namespace DNADesign\Rhino\Fields;

use SilverStripe\Forms\RequiredFields;
use SilverStripe\UserForms\Model\EditableFormField\EditableOption;

class EditableMultiChoiceOption extends EditableOption
{
    private static $db = [
        'IsCorrectAnswer' => 'Boolean'
    ];

    private static $summary_fields = [
        'Value' => 'Title',
        'IsCorrectAnswer' => 'Is Correct Answer?'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Name');
        $fields->removeByName('Default');
        $fields->removeByName('Sort');
        $fields->removeByName('ParentID');
        $fields->removeByName('Title');

        return $fields;
    }

    public function getCMSValidator()
    {
        return RequiredFields::create(['Value']);
    }

    /**
     * Make sure the title is the same as the value for display
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->Title = $this->Value;
    }
}
