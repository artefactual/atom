<?php

/*
 */

class sfDrupalWidgetFormSchemaFormatter extends sfWidgetFormSchemaFormatter
{
  // Heredocs cannot be used to initialize class members,
  // http://php.net/manual/en/language.types.string.php#language.types.string.syntax.nowdoc
  protected
    $errorListFormatInARow = "<div class=\"messages error\">\n  <ul>\n    %errors%\n  </ul>\n</div>\n",
    $helpFormat = "<div class=\"description\">\n  %help%\n</div>\n",
    $name;

  public function getRowFormat()
  {
    // HACK ->formatRow() lacks access to lots of information about the field,
    // including the name.  So to add the name to the row markup, we must
    // either,
    //  * Add it outside ->formatRow(), perhaps using FluentDOM
    //  * Extract the name from the field markup with regex
    //  * Take advantage that ->renderRow() always calls ->renderLabel(), which
    //    calls ->generateLabel(), before ->renderRow()
    return <<<return
<div class="form-item form-item-$this->name">
  %label%
  %error%%field%
  %help%%hidden_fields%
</div>

return;
  }

  public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null)
  {
    if (preg_match('/<input [^>]*type="checkbox"/', $field))
    {
      return parent::formatRow(preg_replace('/<label[^>]*>/', "$0$field", $label), null, $errors, $help, $hiddenFields);
    }

    return parent::formatRow($label, $field, $errors, $help, $hiddenFields);
  }

  public function generateLabelName($name)
  {
    $this->name = $name;

    $label = parent::generateLabelName($name);

    $validatorSchema = $this->form->getValidatorSchema();
    if (isset($validatorSchema[$name]) && $validatorSchema[$name]->getOption('required'))
    {
      $label .= ' <span class="form-required" title="This field is required.">*</span>';
    }

    return $label;
  }
}
