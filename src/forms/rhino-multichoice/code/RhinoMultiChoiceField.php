<?php

namespace DNADesign\Rhino\Fields;

use SilverStripe\Forms\OptionsetField;

/**
 * Custom OptionsetField to be able to customise the template
 */
class RhinoMultiChoiceField extends OptionsetField
{
    protected $sourceField;

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
