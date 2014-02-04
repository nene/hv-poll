<?php

/**
 * Abstract validator, parent of all validators.
 *
 * This is an abstract class - parent of all validator classes.
 * You should not make instances of this class.
 *
 * Error messages:
 *
 * Every validator has an error message associated with him.
 * Validators are meant to only store these error messages
 * and provide access to them, but no validator should have
 * the right to change the value of these.
 *
 */
class Validator
{
     /*** Attributes: ***/
    var $mErrorMessage;

    /**
     * Constructor
     *
     * @param mixed errorMessage
     * Error message is usually a string,
     * but it can be anything. For example you could use array to
     * store simultaneously the same error message in different natural
     * languages, or with different specificity (like one generic
     * message and another one, which explains the details).
     */
    function Validator( $errorMessage )
    {
        $this->setErrorMessage($errorMessage);
    }


    /**
     * setErrorMessage
     *
     * @param mixed errorMessage
     * New value for the associated error message.
     */
    function setErrorMessage( $errorMessage )
    {
        $this->mErrorMessage = $errorMessage;
    }

    /**
     * getErrorMessage
     *
     * @return mixed
     * the error message, associated with an instance of this class.
     */
    function getErrorMessage( )
    {
        return $this->mErrorMessage;
    }
}



/**
 * Validates the minimum length of text.
 *
 * A validator for ensuring, that the string is not shorter than
 * specified by the constructor parameter minLength.
 *
 * Note: this class uses strlen() to determine the strings length,
 * so it can't handle multibyte character sets correctly (especially UTF-8).
 */
class MinLengthValidator extends Validator
{
     /*** Attributes: ***/
    var $mMinLength;

    /**
     * Constructor
     *
     * @param int minLength
     * How short can a string be without becoming invalid.
     * Passing a negative value or zero for this parameter triggers a fatal error.
     *
     * @param mixed errorMessage
     * See class Validator for details.
     */
    function MinLengthValidator( $errorMessage="Text is either too short or too long.", $minLength )
    {
        if ($minLength < 1)
        {
            trigger_error("LengthValidator->LengthValidator: parameter minLength is zero or negative.");
        }

        $this->mMinLength=$minLength;

        parent::Validator($errorMessage);
    }


    /**
     * Validate the minimum length of any given string.
     *
     * @param string text
     * The string who's minumum length you want to validate.
     *
     * @return bool
     */
    function Validate( $text )
    {
        return (strlen($text) >= $this->mMinLength);
    }

}


define("MAX_LENGTH_VALIDATOR_DEFAULT_MAX_MENGTH", 1048576);
/**
 * Validates the maximum length of text.
 *
 * A validator for ensuring, that the string is not longer than
 * specified by the constructor parameter maxLength.
 *
 * Note: this class uses strlen() to determine the strings length,
 * so it can't handle multibyte character sets correctly (especially UTF-8).
 *
 * Constants associated with MaxLengthValidator class:
 *
 * MAX_LENGTH_VALIDATOR_DEFAULT_MAX_MENGTH
 * The default value for maximum length. Equals the number of bytes in a megabyte.
 */
class MaxLengthValidator extends Validator
{
     /*** Attributes: ***/
    var $mMaxLength;

    /**
     * Constructor
     *
     * @param int maxLength
     * How long can a string be without being invalid.
     * The default size is 1MB.
     * Passing a negative value or zero for this parameter triggers a fatal error.
     *
     * @param mixed errorMessage
     * See class Validator for details.
     */
    function MaxLengthValidator( $errorMessage="Text is either too short or too long.", $maxLength = MAX_LENGTH_VALIDATOR_DEFAULT_MAX_MENGTH )
    {
        $maxLenth = (int)$maxLength;

        if ($maxLength < 1)
        {
            trigger_error("MaxLengthValidator->MaxLengthValidator: parameter minLength is zero or negative.");
        }

        $this->mMaxLength=$maxLength;

        parent::Validator($errorMessage);
    }


    /**
     * Validate the maximum length of any given string.
     *
     * @param string text
     * The string who's maximum length you want to validate.
     *
     * @return bool
     */
    function Validate( $text )
    {
        return (strlen($text) <= $this->mMaxLength);
    }

}



/**
 * Validates text against given regular expression.
 *
 */
class RegExValidator extends Validator
{
     /*** Attributes: ***/
    var $mRegEx;

    /**
     * Constructor
     *
     * @param string regEx
     * A perl compatible regular expression, against which the string,
     * passed to method Validate(), is tested. For example to create a validator,
     * which accepts only english uppercase letters:
     * <code>$validator = new RegExValidator('/^[A-Z]*$/');</code>
     *
     * @param mixed errorMessage
     * See class Validator for details.
     */
    function RegExValidator( $errorMessage="Text does not match a pattern.", $regEx )
    {
        $this->mRegEx=$regEx;

        parent::Validator($errorMessage);
    }


    /**
     * Validate any given string against the pattern given in constructor.
     *
     * @param string text
     * The string you want to validate.
     *
     * @return bool
     */
    function Validate( $text )
    {
        return preg_match($this->mRegEx, $text);
    }

}



/**
 * A validator for checking if a form field was filled in.
 *
 * Validate() returnes true if string is not empty.
 * Strings consisting of spacing characters only are considered empty.
 *
 * This validator has the exact same functionality as:
 * <code>$validator = new RegExValidator('', '/\S/')</code>
 */
class RequiredFieldValidator extends RegExValidator
{

    /**
     * Constructor
     *
     * @param mixed errorMessage
     * See class Validator for details.
     */
    function RequiredFieldValidator( $errorMessage="This is a required field an must be filled." )
    {
        parent::RegExValidator($errorMessage, '/\S/' );
    }

}



/**
 * A validator for checking the correctness of an e-mail address.
 *
 * This class simply calls the constructor of RegExValidator,
 * passing one long regular expression to it. Use it instead
 * of RegExValidator when you need to check the syntax of e-mail addresses.
 */
class EMailAddressValidator extends RegExValidator
{

    /**
     * Constructor
     *
     * @param mixed errorMessage
     * See class Validator for details.
     */
    function EMailAddressValidator( $errorMessage="This is not a correct e-mail address." )
    {
        parent::RegExValidator($errorMessage,
                               '/^[A-Z0-9._-]+@'.                   // username
                               '[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]'.  // lowel level domain name part i.e. example.co
                               '\.[A-Z]{2,6}\.?$/i'                 // domain name i.e. .com .ee.
                              );
    }

}



/**
 * A validator for checking the correctness of an URL.
 *
 * Only ftp, sftp, http and https protocols are accepted as valid.
 * If you need anything beyond that, derive your own specific
 * validator class from RegExValidator - don't modify this class
 * to include more protocols. Anyway... this class does nothing
 * more than calling the RegExValidator's constructor with
 * appropriate regular expression.
 */
class UrlValidator extends RegExValidator
{

    /**
     * Constructor
     *
     * @param mixed errorMessage
     * See class Validator for details.
     */
    function UrlValidator( $errorMessage="This is not a correct URL." )
    {
        parent::RegExValidator($errorMessage,
                               '/^(S?FTP|HTTPS?):\/\/'.            // protocol i.e. http://
                               '[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]'. // lowel level domain name part
                               '\.[A-Z]{2,6}\.?'.                  // domain name i.e. .com .ee.
                               '\/?.*$/i'                           // everything else that follows
                              );
    }

}



/**
 * Checks if the given value is in the list (range) of allowed values.
 *
 */
class RangeValidator extends Validator
{
     /*** Attributes: ***/
    var $mValueList;

    /**
     * Constructor
     *
     * @param mixed errorMessage
     * See class Validator for details.
     *
     * @param array valueList
     * An array with structure ( label1 => value1, label2 => value2, ... )
     * Where where label is the text visible in the HTML form, and value
     * the corresponing value. For example like this:
     * <code>&lt;option value="value1"&gt;label1&lt;/option&gt;</code>
     * NB! The list can not be empty - or an error will be triggered.
     *
     */
    function RangeValidator( $errorMessage="The specified value is out of the valid range.", $valueList )
    {
        if (count($valueList)==0)
        {
            trigger_error("RangeValidator->RangeValidator: Parameter 'valueList' contains an empty array.");
        }
        $this->mValueList = $valueList;

        parent::Validator($errorMessage);
    }



    /**
     * Check if the string exists in a specified set.
     *
     * @param string text
     * The string who's existance you want to validate.
     *
     * @return bool
     */
    function Validate( $text )
    {
        return in_array( $text, $this->mValueList );
    }


}



// these are all private constants:
define("DATE_VALIDATOR_YEAR", 0);
define("DATE_VALIDATOR_MONTH", 1);
define("DATE_VALIDATOR_DAY", 2);
/**
 * Checks if the supplied text contains a valid date.
 *
 */
class DateValidator extends Validator
{

    /**
     * Constructor
     *
     * @param mixed errorMessage
     * See class Validator for details.
     */
    function DateValidator( $errorMessage="This is not a correct date." )
    {

        parent::Validator($errorMessage);
    }


    /**
     * Check if the supplied text contains valid date, formatted as YYYY-MM-DD
     *
     * @param string text
     * The string you want to validate.
     *
     * @return bool
     */
    function Validate( $text )
    {
        $date=explode("-", $text);

        return checkdate( (int)$date[DATE_VALIDATOR_MONTH], (int)$date[DATE_VALIDATOR_DAY], (int)$date[DATE_VALIDATOR_YEAR] );
    }

}

?>
