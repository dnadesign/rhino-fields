<?php

class EditableMultiChoiceOption extends EditableOption {

	private static $db = array(
		'IsCorrectAnswer' => 'Boolean'
	);

	private static $summary_fields = array(
		'Value' => 'Title',
		'IsCorrectAnswer' => 'Is Correct Answer?'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('Name');
		$fields->removeByName('Default');
		$fields->removeByName('Sort');
		$fields->removeByName('ParentID');
		$fields->removeByName('Title');

		return $fields;
	}

	public function getCMSValidator() {
		return new RequiredFields(array('Value'));
	}

	/**
	* Make sure the title is the same as the value for display
	*/
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->Title = $this->Value;
	}
}