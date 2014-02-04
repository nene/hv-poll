<?php
require_once("selectfields.php");
require_once("validators.php");

/**
 * class DateField
 */
class DateField extends TextField
{

     /*** Attributes: ***/
    var $mYear;
    var $mMonth;
    var $mDay;


    /**
     * Constructor
     *
     * @param string name
     * See class FormField for details.
     *
     * @param array months
     * Array which contains all 12 months, with structure like:
     * Array("January" => "01", "February" => "02", ... , "December" => "12")
     *
     * @param string defaultValue
     * The string which contains the default date for a DateField
     * See class FormField for more details.
     *
     * @param string method
     * See class FormField for details.
     */
    function DateField( $name,  $months,  $defaultValue="",  $method=FORM_FIELD_METHOD_POST )
    {
        // create three separate fields for year, month and day
        $this->mYear = new TextField("$name-year");
        $this->mYear->AddValidator(new RequiredFieldValidator());

        $this->mMonth = new SelectField("$name-month", $months, "01");
        $this->mMonth->AddValidator(new RangeValidator("error", $months));

        $this->mDay = new TextField("$name-day");
        $this->mDay->AddValidator(new RequiredFieldValidator());

        parent::TextField($name, $defaultValue, $method);
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
        $this->mYear->Validate();
        $this->mMonth->Validate();
        $this->mDay->Validate();

        if ( $this->mYear->IsValid() &&
             $this->mMonth->IsValid() &&
             $this->mDay->IsValid()
           )
        {
            $this->mValue = $this->mYear->getValue();
            $this->mValue.= "-";
            $this->mValue.= $this->mMonth->getValue();
            $this->mValue.= "-";

            // if day has no leading zero, then append it.
            if ( strlen($this->mDay->getValue())==1 )
            {
                $this->mValue.= "0".$this->mDay->getValue();
            }
            else
            {
                $this->mValue.= $this->mDay->getValue();
            }
        }
        else
        {
            $this->mValue = "";
        }
    }

    /**
     *
     * @return array
     */
    function getFormControl( )
    {
        $year  = $this->mYear->getFormControl();
        $month = $this->mMonth->getFormControl();
        $day   = $this->mDay->getFormControl();

        return Array("year"=>$year,
                     "month"=>$month,
                     "day"=>$day
                    );
    }

}

/**
 * class EstDateField
 */
class EstDateField extends DateField
{

    /**
     * Constructor
     *
     * @param string name
     * See class FormField for details.
     *
     * @param string defaultValue
     * The string which contains the default date for a DateField
     * See class FormField for more details.
     *
     * @param string method
     * See class FormField for details.
     */
    function EstDateField( $name,  $defaultValue="",  $method=FORM_FIELD_METHOD_POST )
    {
        $months = Array("Jaanuar"=>"01",
                        "Veebruar"=>"02",
                        "Märts"=>"03",
                        "Aprill"=>"04",
                        "Mai"=>"05",
                        "Juuni"=>"06",
                        "Juuli"=>"07",
                        "August"=>"08",
                        "September"=>"09",
                        "Oktoober"=>"10",
                        "November"=>"11",
                        "Detsember"=>"12"
                       );

        parent::DateField($name, $months, $defaultValue, $method);
    }
}

?>
