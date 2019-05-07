<?php

namespace DNADesign\Rhino\Fields;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\LiteralField;
use SilverStripe\UserForms\Model\EditableFormField\EditableMultipleOptionField;
use SilverStripe\Versioned\Versioned;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class EditableOrderableOptionsField extends EditableMultipleOptionField implements RhinoMarkedField
{
    private static $singular_name = 'Orderable Options Field';

    private static $min_options = -1;

    private static $max_options = -1;

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('MergeField');
        $fields->removeByName('Name');
        $fields->removeByName('Options.Options');
        $fields->removeByName('Options');

        $showWarning = ($this->config()->get('min_options') && $this->config()->get('min_options') >= 0);

        /**
         * Show a warning only if there are more than one option
         * or if none of the option have been set as the correct answer
         */
        if ($showWarning === true && $this->Options()->Count() >= $this->config()->get('min_options')) {
            $showWarning = false;
        }

        if ($showWarning) {
            $fields->addFieldToTab(
                'Root.Main',
                new LiteralField(
                    'warning',
                    '<p class="message warning">
						<strong>
							Please add 3 or more options.
						</strong>
					</p>'
                )
            );
        }

        $config = GridFieldConfig_RecordEditor::create();
        $config->addComponent(new GridFieldOrderableRows('Sort'));

        /**
         * If we have reached the max amount of options
         * remove GridFieldAddNewButton
         */
        if ($this->config()->get('max_options')
            && $this->config()->get('max_options') >= 0
            && $this->Options()->Count() >= $this->config()->get('max_options')
        ) {
            $config->removeComponentsByType('GridFieldAddNewButton');
            $fields->addFieldToTab(
                'Root.Main',
                new LiteralField(
                    'warning',
                    '<p class="message warning">
						<strong>
							' . sprintf('You cannot add more than %s options.', $this->config()->get('max_options')) . '
						</strong>
					</p>'
                )
            );
        }

        $gridfield = GridField::create(
            'Options',
            'Options',
            $this->Options(),
            $config
        );

        $fields->addFieldToTab('Root.Main', $gridfield);

        return $fields;
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
        $field = RhinoOrderableOptionsField::create($this->Name, $this->EscapedTitle, $this->getOptionsMap());
        $field->setSourceField($this);

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
        $options = $this->Options();

        // Always Randomise options
        do {
            $optionsID = $options->column('ID');
            shuffle($optionsID);
        } while ($optionsID === $options->column('ID'));

        $ids = implode(',', $optionsID);

        $stage = (Versioned::current_stage() == 'Live') ? '_Live' : '';
        $sort = sprintf('FIELD(%s,%s)', 'EditableOption' . $stage . '.ID', $ids);

        $options = $options->alterDataQuery(function ($query) use ($sort) {
            $query->sort($sort);
        });

        return $options;
    }

    /**
     * Compare the order of the options on the Editable Field
     * and the options giden by the form
     *
     * @param String | JSON array
     * @return Boolean
     */
    public function pass_or_fail($value = null)
    {
        $expected = $this->Options()->column('ID');
        $given = json_decode($value);

        $mark = ($expected === $given) ? 'pass' : 'fail';
        $this->extend('updateMark', $value, $mark);

        return $mark;
    }

}
