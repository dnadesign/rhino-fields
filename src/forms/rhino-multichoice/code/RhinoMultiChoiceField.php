<?php

namespace DNADesign\rhinofields;

use DNADesign\rhinofields\RhinoMarkedField;
use DNADesign\rhinofields\EditableMultiChoiceOption;
use EditableRadioField;
use GridFieldConfig_RelationEditor;
use GridFieldDataColumns;
use GridFieldOrderableRows;
use GridField;
use CheckboxField;
use OptionsetField;
use LiteralField;

class EditableMultiChoiceField extends EditableRadioField implements RhinoMarkedField {

	private static $table_name = 'EditableMultiChoiceField';

	private static $singular_name = 'Multi Choice Field';

	private static $optionClass = 'DNADesign\rhinofields\EditableMultiChoiceOption';

	private static $db = array(
		'RandomiseOptions' => 'Boolean'
	);

	private static $casting = array(
		"Options" => 'EditableMultiChoiceOption'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('MergeField');
		$fields->removeByName('Name');
		$fields->removeByName('Options.Options');
		$fields->removeByName('Options');

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

		if($showWarning) {
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

		$config->removeComponentsByType('GridFieldDataColumns');
		$config->removeComponentsByType('GridFieldFilterHeader');
		$config->removeComponentsByType('GridFieldAddExistingAutocompleter');

		$dataColumns = new GridFieldDataColumns();
		$dataColumns->setDisplayFields(array(
			'Value' => 'Title',
			'IsCorrectAnswer' => 'Is Correct Answer?'
		));

		$dataColumns->setFieldFormatting(array(
			'IsCorrectAnswer' => function($value, $item) {
				return ($value) ? '<strong>Yes</strong>' : 'No';
			}
		));

		$config->addComponent($dataColumns, 'GridFieldEditButton');
		$config->addComponent(new GridFieldOrderableRows('Sort'));

		$gridfield = GridField::create(
			'Options',
			'Options',
			$this->Options(),
			$config
		)->setModelClass(self::$optionClass);

		$fields->addFieldToTab('Root.Main', $gridfield);

		// Randomise
		$random = CheckboxField::create('RandomiseOptions');
		$fields->addFieldToTab('Root.Main', $random, 'Options');

		return $fields;
	}

	public function onBeforeWrite() {
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
	public function getFormField() {
		$field = OptionsetField::create($this->Name, $this->EscapedTitle, $this->getOptionsMap());
		return $field;
	}

	/**
	* Need to pass the Option object themselves
	* in order to use all the data available on the
	* EditableOption object
	*
	* @return Array
	*/
	protected function getOptionsMap() {
		$optionClass = $this->config()->optionClass;
		$options = $optionClass::get();

		// Randomise options if required
		if ($this->RandomiseOptions) {
			$optionsID = $this->Options()->column('ID');
			shuffle($optionsID);

			$ids = implode(',', $optionsID);

			$stage = (Versioned::current_stage() == 'Live') ? '_Live' : '';
			$sort = sprintf('FIELD(%s,%s)', 'EditableOption'.$stage.'.ID', $ids); 				

			$options = $options->alterDataQuery(function($query) use ($sort) {				
				$query->sort($sort);
			});
		}

		return $options->map('Value', 'Title');
	}

	/**
	* Return All the correct answers for this field
	*
	* @return DataList (EditableMultiChoiceOption)
	*/
	public function getCorrectAnswers() {
		$optionClass = $this->config()->optionClass;
		return $optionClass::get()->filter('IsCorrectAnswer', true);
	}

	/**
	* Check if the asnwer given matches the expected one
	*/
	public function pass_or_fail($value = null) {
		if (!$value) return null;
		if ($this->getCorrectAnswers()->Count() == 0) return 'pass';

		$expected = $this->getCorrectAnswers()->column('Value');

		$mark = (in_array($value, $expected)) ? 'pass' : 'fail';

		$this->extend('updateMark', $value, $mark);

		return $mark;
	}

}