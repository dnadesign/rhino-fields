<?php

namespace DNADesign\Rhino\Fields;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldPageCount;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\LiteralField;
use SilverStripe\UserForms\Model\EditableFormField\EditableRadioField;
use SilverStripe\Versioned\Versioned;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class EditableMultiChoiceField extends EditableRadioField implements RhinoMarkedField
{
    private static $singular_name = 'Multi Choice Field';

    private static $optionClass = EditableMultiChoiceOption::class;

    private static $table_name = 'EditableMultiChoiceField';

    private static $db = [
        'RandomiseOptions' => 'Boolean'
    ];

    private static $has_one = [
        'Image' => Image::class
    ];

    private static $owns = [
        'Image'
    ];

    private static $casting = [
        "Options" => 'EditableMultiChoiceOption'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'MergeField',
            'Name',
            'Options.Options',
            'Options'
        ]);

        // Image
        $image = UploadField::create('Image', 'Image');
        $fields->addFieldToTab('Root.Main', $image, 'RightTitle');

        $showWarning = true;

        /**
         * Show a warning only if there are more than one option
         * or if none of the option have been set as the correct answer
         */
        if ($this->Options()->Count() < 2) {
            $showWarning = false;
        } else {
            $showWarning = ($this->getCorrectAnswers()->Count() == 0);
        }

        if ($showWarning) {
            $fields->addFieldToTab(
                'Root.Main',
                new LiteralField(
                    'warning',
                    '<p class="message warning">
						<strong>
							No correct answers selected. If no correct answers then all answers will be treated as correct
						</strong>
					</p>'
                )
            );
        }

        $config = GridFieldConfig_RelationEditor::create();

        $config->removeComponentsByType([
            GridFieldFilterHeader::class,
            GridFieldAddExistingAutocompleter::class,
            GridFieldPageCount::class,
            GridFieldPaginator::class
        ]);
        $config->addComponent(new GridFieldOrderableRows('Sort'));

        $gridfield = GridField::create(
            'Options',
            'Options',
            $this->Options(),
            $config
        )->setModelClass($this->config()->optionClass);

        $fields->addFieldToTab('Root.Main', $gridfield);

        // Randomise
        $random = CheckboxField::create('RandomiseOptions');
        $fields->addFieldToTab('Root.Main', $random, 'Options');

        return $fields;
    }

    /**
     * Return All the correct answers for this field
     *
     * @return DataList (EditableMultiChoiceOption)
     */
    public function getCorrectAnswers()
    {
        $optionClass = $this->config()->optionClass;

        return $optionClass::get()->filter(array('IsCorrectAnswer' => true, 'ParentID' => $this->owner->ID));
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->Value = $this->Title;
    }

    /**
     * Use the custom class for OptionsetField in order to use Objects instead of flat arrays
     * and set the custom template for it
     * and pass the Image object to the template for the field itself
     *
     * @return CustomOptionsetField
     */
    public function getFormField()
    {
        $field = RhinoMultiChoiceField::create($this->Name, $this->EscapedTitle, $this->getOptionsMap());
        $field->setSourceField($this);

        if ($this->Image()->exists()) {
            $field->customise(array('Image' => $this->Image(), 'Source' => $this));
        }

        return $field;
    }

    /**
     * Need to pass the Option object themselves
     * in order to use all the data available on the
     * EditableOption object
     *
     * @return Array
     */
    protected function getOptionsMap()
    {
        $optionClass = $this->config()->optionClass;
        $options = $optionClass::get()->filter('ParentID', $this->ID);

        // Randomise options if required
        if ($this->RandomiseOptions) {
            $optionsID = $this->Options()->column('ID');
            shuffle($optionsID);

            $ids = implode(',', $optionsID);

            $stage = (Versioned::get_stage() == 'Live') ? '_Live' : '';

            $sort = sprintf('FIELD(%s,%s)', 'EditableOption' . $stage . '.ID', $ids);

            $options = $options->alterDataQuery(function ($query) use ($sort) {
                $query->sort($sort);
            });
        }

        return $options->map('Value', 'Title')->toArray();
    }

    /**
     * Check if the asnwer given matches the expected one
     *
     * @param String
     * @return String
     */
    public function pass_or_fail($value = null)
    {
        if (!$value) {
            return null;
        }

        // Find the ID of the expect answers
        $expected = $this->getCorrectAnswers()->column('ID');

        // If no right answer is supplied, pass by default
        if (empty($expected)) {
            return 'pass';
        }

        // Find the Editable Option ID object from the value given
        $given = $this->getAnswerForValue($value);

        // Check if the ID of the given option belongs to the expected Option IDs
        $mark = ($given && $given->exists() && in_array($given->ID, $expected)) ? 'pass' : 'fail';

        $this->extend('updateMark', $value, $mark, $given);

        return $mark;
    }

    /**
     * Return the First the answers for this field for a given value
     * We assume that there isn't 2 answers with the same value
     *
     * @param String
     * @return EditableOption
     */
    public function getAnswerForValue($value)
    {
        $optionClass = $this->config()->optionClass;

        return $optionClass::get()->filter(array('Value' => $value, 'ParentID' => $this->ID))->First();
    }
}
