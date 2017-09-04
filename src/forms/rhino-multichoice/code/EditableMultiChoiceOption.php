<?php

namespace DNADesign\rhinofields;

use EditableOption;
use RequiredFields;

class EditableMultiChoiceOption extends EditableOption {

	private static $table_name = 'EditableMultiChoiceOption';

	private static $db = array(
		'IsCorrectAnswer' => 'Boolean'
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