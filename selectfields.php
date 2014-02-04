<?php
require_once("formfields.php");


/**
 * class SelectField
 *
 * Note: one validation is automatically performed inside this class.
 * The class checks if the value belongs to the list of values.
 */
class SelectField extends FormField
{

     /*** Attributes: ***/
    var $mValueList = Array();


    /**
     * Constructor
     *
     * @param string name
     * See class FormField for details.
     *
     * @param array valueList
     * An array with structure ( label1 => value1, label2 => value2, ... )
     * Where where label is the text visible in the HTML form, and value
     * the corresponing value. For example like this:
     * <code>&lt;option value="value1"&gt;label1&lt;/option&gt;</code>
     * NB! The list can not be empty - or an error will be triggered.
     *
     * @param string defaultValue
     * The currently selected value from the valueList.
     * By default the first element from the list is used.
     *
     * @param int method
     * See class FormField for details.
     */
    function SelectField( $name,  $valueList,  $defaultValue="",  $method=FORM_FIELD_METHOD_POST )
    {
        if (count($valueList)==0)
        {
            trigger_error("SelectField->SelectField: Parameter 'valueList' contains an empty array.");
        }
        $this->mValueList = $valueList;

        // if default value is not specified, use the first value from valueList
        if ($defaultValue=="")
        {
            $defaultValue=$valueList[0];
        }

        parent::FormField($name, $defaultValue, $method);
    }


    /**
     *
     * @return string
     */
    function getFormControl( )
    {
        $one_already_selected=false;

        $result = "<select name=\"{$this->mName}\">\n";
        foreach ($this->mValueList as $label => $value)
        {
            if ( $this->mValue == $value && !$one_already_selected)
            {
                $selected=' selected="selected"';
                $one_already_selected=true;
            }
            else
            {
                $selected='';
            }

            $result.= "<option value=\"$value\"$selected>$label</option>";
        }
        $result.= "\n</select>";

        return $result;
    }


}

/**
 * class RadioSelectField
 */
class RadioSelectField extends SelectField
{
    /**
     *
     * @return string
     */
    function getFormControl( )
    {
        $result = "<ul>\n";
        foreach ($this->mValueList as $label => $value)
        {
            if ( $this->mValue == $label )
            {
                $checked=' checked="checked"';
            }
            else
            {
                $checked='';
            }

            $result.= <<<EOHTML
<li><label><input type="radio" name="{$this->mName}" value="$value"$checked /> $label</label></li>
EOHTML;
        }
        $result.= "\n</ul>";

        return $result;
    }
}




/**
 * class RangeSelectField
 */
class RangeSelectField extends SelectField
{

}



/**
 * class MultipleSelectField
 */
class MultipleSelectField extends SelectField
{


}

?>
