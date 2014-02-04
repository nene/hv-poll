<?php
require_once("validators.php");

define("FORM_FIELD_METHOD_GET", 1);
define("FORM_FIELD_METHOD_POST", 2);
/**
 * class FormField
 *
 * This is an abstract class - parent of all Field classes.
 * You should not make instances of this class.
 *
 * Constants associated with FormField class:
 *
 * FORM_FIELD_METHOD_GET
 * Designates, that HTML form uses GET-method.
 *
 * FORM_FIELD_METHOD_POST
 * Designates, that HTML form uses POST-method.
 */
class FormField
{
     /*** Attributes: ***/
    var $mName;
    var $mValue;
    var $mMethod;
    var $mValidators = Array();
    var $mErrorMessage="";
    var $mIsValid=true;

    /**
     * Constructor
     *
     * @param string name
     * The value for the attribute 'name' in HTML form element.
     * It's probably most meaningful to supply the same name you give
     * for your class instance in your PHP code. i.e. :
     * <code>$my_field = new TextField("my_field");</code>
     *
     * @param mixed defaultValue
     * The default value for your form element.
     * Check the specific subclasses to see what type of data is needed
     * and/or permitted. You should use this parameter when you need
     * the form element to contain some initial value.
     * Default is an empty string.
     *
     * @param int method
     * Specifies the value of the 'method' attribute in your HTML form-element.
     * Method must be either FORM_FIELD_METHOD_GET or FORM_FIELD_METHOD_POST.
     * FORM_FIELD_METHOD_POST is the default.
     * Suppling any other value will trigger an error.
     * For example when you specify FORM_FIELD_METHOD_POST, but your HTML form
     * has method="GET", then the script tries to find the data, sent by the form,
     * from superglobal $_POST, but will fail, and therefore will it act like the
     * form field had been empty.
     */
    function FormField( $name,  $defaultValue="",  $method=FORM_FIELD_METHOD_POST )
    {
        $this->mName=$name;
        $this->mValue=$defaultValue;

        if ( !($method==FORM_FIELD_METHOD_GET || $method==FORM_FIELD_METHOD_POST) )
        {
            trigger_error("FormField->FormField: Parameter 'method' is not FORM_METHOD_GET or FORM_METHOD_POST.");
        }
        $this->mMethod=$method;
    }


    /**
     * Associates new validator with form field.
     *
     * @param Validator validator
     * A validator object, for which a specific validation task is assigned.
     */
    function AddValidator( $validator )
    {
        $this->mValidators[] = $validator;
    }


    /**
     * Get the value for the mValue attribute from either
     * $_GET or $_POST array - specified in the mMethod attribute.
     * If the value is not found from the array, assign an empty string.
     *
     * @access protected
     */
    function AquireFieldValue( )
    {
        // If the method is POST, then get the value form $_POST, otherwise from $_GET.
        // If both fail, assing an empty string.
        if ( $this->mMethod==FORM_FIELD_METHOD_POST && isset($_POST[$this->mName]) )
        {
            $this->mValue = $_POST[$this->mName];
        }
        elseif ( $this->mMethod==FORM_FIELD_METHOD_GET && isset($_GET[$this->mName]) )
        {
            $this->mValue = $_GET[$this->mName];
        }
        else
        {
            $this->mValue = "";
        }
    }


    /**
     *
     * @return bool
     */
    function Validate( )
    {
        $this->AquireFieldValue();

        foreach ($this->mValidators as $validator)
        {
            if ( !($validator->Validate($this->mValue)) )
            {
                $this->mErrorMessage = $validator->getErrorMessage();
                $this->mIsValid = false;
                return false;
            }
        }

        return true;
    }


    /**
     *
     * @return bool
     */
    function IsValid( )
    {
        return $this->mIsValid;
    }


    /**
     *
     * @return mixed
     */
    function getErrorMessage( )
    {
        return $this->mErrorMessage;
    }


    /**
     *
     * @return mixed
     */
    function getValue( )
    {
        return $this->mValue;
    }

}



/**
 * class TextField
 */
class TextField extends FormField
{

    /**
     * Constructor
     *
     * @param string name
     * See class FormField for details.
     *
     * @param string defaultValue
     * The string which contains the default text for a TextField.
     * The string must not contain linebreaks,
     * use MultiLineField if you need multiple lines of text.
     * See class FormField for more details.
     *
     * @param string method
     * See class FormField for details.
     */
    function TextField( $name,  $defaultValue="",  $method=FORM_FIELD_METHOD_POST )
    {
        parent::FormField($name, $defaultValue, $method);
    }

    /**
     *
     * @return string
     */
    function getFormControl( )
    {
        return <<<EOHTML
<input type="text" name="{$this->mName}" value="{$this->mValue}" />
EOHTML;
    }


}



/**
 * class MultilineField
 */
class MultiLineField extends TextField
{

     /*** Attributes: ***/
    var $mRows;
    var $mCols;

    /**
     * Constructor
     *
     * @param string name
     * See class FormField for details.
     *
     * @param int rows
     * The number of rows in HTML textarea element.
     *
     * @param int cols
     * The number of columns in HTML textarea element.
     *
     * @param string defaultValue
     * The string which contains the default text for a MultilineField.
     * See class FormField for more details.
     *
     * @param string method
     * See class FormField for details.
     */
    function MultiLineField( $name,  $rows=15,  $cols=50,  $defaultValue="",  $method=FORM_FIELD_METHOD_POST )
    {
        $this->mRows=(int)$rows;
        $this->mCols=(int)$cols;
        parent::TextField($name, $defaultValue, $method);
    }

    /**
     *
     * @return string
     */
    function getFormControl( )
    {
        return <<<EOHTML
<textarea name="{$this->mName}" rows="{$this->mRows}" cols="{$this->mCols}">{$this->mValue}</textarea>
EOHTML;
    }

}



/**
 * class PasswordField
 */
class PasswordField extends TextField
{

    /**
     *
     * @return string
     */
    function getFormControl( )
    {
        return <<<EOHTML
<input type="password" name="{$this->mName}" value="{$this->mValue}" />
EOHTML;
    }

}

/**
 * class CheckBoxField
 */
class CheckBoxField extends FormField
{


    /**
     *
     * @return string
     */
    function getFormControl( )
    {
        if ( $this->mValue == "on" )
        {
            return <<<EOHTML
<input type="checkbox" name="{$this->mName}" checked="checked" />
EOHTML;
        }
        else
        {
            return <<<EOHTML
<input type="checkbox" name="{$this->mName}" />
EOHTML;
        }
    }

}



?>
