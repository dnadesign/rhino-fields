<?php

namespace DNADesign\rhinofields;

/**
* ReadOnly field that display the elapsed time 
* starting when the page is loaded
* Javascript is hardcoded on the template
*/

use DNADesign\rhinofields\RhinoMarkedField;
use EditableFormField;
use TimeField;
use TextField;

class RhinoTimerField extends EditableFormField implements RhinoMarkedField {

	private static $table_name = 'RhinoTimerField';

	private static $hidden = false;

	private static $singular_name = 'Timer';

	private static $defaults = array(
		'Default' => '00:00:00'
	);

	private static $db = array(
		'TimeLimit' => 'Time'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName('MergeField');
		$fields->removeByName('Name');

		$time = TimeField::create('TimeLimit')
			->setConfig('timeformat', 'HH:mm:ss')
			->setRightTitle('hh:mm:ss');
		$fields->addFieldToTab('Root.Main', $time);

		return $fields;
	}

	/**
	 * @return TextareaField|TextField
	 */
	public function getFormField() {
		$field = TextField::create($this->Name, $this->EscapedTitle, $this->Default)
				->setFieldHolderTemplate('UserFormsField_holder')
				->setTemplate('RhinoTimerField')
				->setValue('00:00:00');

		 $this->doUpdateFormField($field);

		return $field;
	}

	/**
	* If the timevalue exceeds the Time limit
	* Return a Fail mark
	*/
	public function pass_or_fail($value = null) {
		if (!$value) return null;
		if (!$this->TimeLimit) return 'pass';

		$limit = strtotime($this->TimeLimit);
		$timed = strtotime($value);

		$mark = null;

		// Compare times
		if ($limit || $timed)  {
			$mark =  ($timed > $limit) ? 'fail' : 'pass';
		}

		$this->extend('updateMark', $value, $mark);

		return $mark;
	} 

}