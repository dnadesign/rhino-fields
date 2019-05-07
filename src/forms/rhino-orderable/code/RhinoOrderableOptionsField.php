<?php

namespace DNADesign\Rhino\Fields;

use SilverStripe\Forms\TextField;

class RhinoOrderableOptionsField extends TextField
{
    protected $options;
    protected $sourceField;

    public function __construct($name, $title = null, $options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }

        $initialValue = ($options) ? json_encode($options->column('ID')) : null;

        parent::__construct($name, $title, $initialValue);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function getSourceField()
    {
        return $this->sourceField;
    }

    public function setSourceField($field)
    {
        $this->sourceField = $field;

        return $this;
    }
}
