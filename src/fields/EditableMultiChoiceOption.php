<?php

namespace DNADesign\Rhino\Fields;

use SilverStripe\Forms\CompositeValidator;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\UserForms\Model\EditableFormField\EditableOption;

class EditableMultiChoiceOption extends EditableOption
{
    private static $table_name = 'EditableMultiChoiceOption';

    private static $db = [
        'IsCorrectAnswer' => 'Boolean'
    ];

    private static $summary_fields = [
        'Value' => 'Title',
        'IsCorrectAnswer.Nice' => 'Is Correct Answer?'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(
            [
            'Name',
            'Default',
            'Sort',
            'ParentID',
            'Title'
            ]
        );

        return $fields;
    }

    public function getCMSCompositeValidator() : CompositeValidator
    {
        $validator = parent::getCMSCompositeValidator();

        $validator->addValidator(new RequiredFields(['Value']));

        return $validator;
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
