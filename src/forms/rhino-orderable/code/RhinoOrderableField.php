<?php

class EditableOrderableOptionsField extends EditableMultipleOptionField implements RhinoMarkedField {

	private static $singular_name = 'Orderable Options Field';

	private static $default_min_options = 3;

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
		if ($this->Options()->Count() >= $this->default_min_options) {
			$showWarning = false;
		} 

		if($showWarning) {
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

		$config = GridFieldConfig_RelationEditor::create();
		$config->addComponent(new GridFieldOrderableRows('Sort'));

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
	public function getFormField() {
		$field = RhinoOrderableOptionsField::create($this->Name, $this->EscapedTitle, $this->getOptionsMap());
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
		$options = $this->Options();

		// Always Randomise options
		do {
			$optionsID = $options->column('ID');
			shuffle($optionsID);
		} while ($optionsID === $options->column('ID'));		

		$ids = implode(',', $optionsID);

		$stage = (Versioned::current_stage() == 'Live') ? '_Live' : '';
		$sort = sprintf('FIELD(%s,%s)', 'EditableOption'.$stage.'.ID', $ids); 				

		$options = $options->alterDataQuery(function($query) use ($sort) {				
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
	public function pass_or_fail($value = null) {
		$expected = $this->Options()->column('ID');
		$given = json_decode($value);
		return ($expected === $given);
	}
	
}

class RhinoOrderableOptionsField extends TextField {

	protected $options;

	public function __construct($name, $title = null, $options = null) {
		if ($options) {
			$this->setOptions($options);
		}

		$initialValue = ($options) ? json_encode($options->column('ID')) : null;

		parent::__construct($name, $title, $initialValue);
	}

	public function setOptions($options) {
		$this->options = $options;
	}

	public function getOptions() {
		return $this->options;
	}

}