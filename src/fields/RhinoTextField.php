<?php

namespace DNADesign\Rhino\Fields;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;

class RhinoTextField extends EditableTextField implements RhinoMarkedField
{
    private static $table_name = 'RhinoTextField';

    private static $hidden = false;

    private static $singular_name = 'TextField (Marked)';

    private static $db = array(
        'Answers' => 'Varchar(255)',
        'CaseSensitive' => 'Boolean',
        'AcceptSentence' => 'Boolean'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Answer
        $answer = TextField::create('Answers');
        $answer->setRightTitle('Comma seperated list of expected keywords');

        // Case Sensitive
        $case = CheckboxField::create('CaseSensitive');
        $case->setRightTitle('Answer is case sensitive');

        // Sentence
        $sentence = CheckboxField::create('AcceptSentence');
        $sentence->setRightTitle('Answer will be correct if sentence contains one or more keywords');

        $fields->addFieldsToTab('Root.Config', array($answer, $case, $sentence));

        return $fields;
    }

    /**
     * Check if the asnwer given matches the expected one
     */
    public function pass_or_fail($value = null)
    {
        if (!$value) {
            return null;
        }
        if (!$this->Answers) {
            return 'pass';
        }

        $expected = ($this->CaseSensitive) ? $this->Answers : strtolower($this->Answers);
        $expected = array_map('trim', explode(',', $expected));

        $answer = ($this->CaseSensitive) ? $value : strtolower($value);
        $mark = null;

        // Check single value
        if (!$this->AcceptSentence) {
            $mark = (in_array($answer, $expected)) ? 'pass' : 'fail';
        }
        // If a senentece is given
        // look for the value within it
        else {
            $matches = 0;
            foreach ($expected as $keyword) {
                if ((strpos($answer, $keyword) !== false)) {
                    $matches++;
                }
            }

            $mark = ($matches > 0) ? 'pass' : 'fail';
        }

        $this->extend('updateMark', $value, $mark);

        return $mark;
    }

}
