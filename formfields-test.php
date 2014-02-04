<?php
require_once("selectfields.php");
require_once("datefield.php");

// Create new InputField instance
// The string "phone" will be the the value of the name-attribute
// of HTML element INPUT.
$phone_nr = new TextField("phone");

// Define, that the field must be filled
$phone_nr->AddValidator(new RequiredFieldValidator("This field must be filled"));

// Define, that the value entered can be no longer then 15 characters
$phone_nr->AddValidator(new MaxLengthValidator("no more than 15 letters allowed", 15));

// Define, that the value entered must be numeric
$phone_nr->AddValidator(new RegExValidator("only numbers are allowed", '/^[0-9]+$/'));


$aastad = Array(
    "---"=>"---",
    2005=>2005,
    2004=>2004,
    2003=>2003,
    2002=>2002,
    2001=>2001,
    2000=>2000,
);
$select = new SelectField("aasta", $aastad, "2000");
$select->AddValidator(new RangeValidator("You fucking hacker!", $aastad));
$select->AddValidator(new RegExValidator("You must select a year!", '/^[0-9]*$/'));

$date = new EstDateField("date");
$date->AddValidator(new RequiredFieldValidator());
$date->AddValidator(new DateValidator("Incorrect date"));

// Check if form is posted back
if (isset($_POST['submit']))
{
    // Perform the validations of the field
    // NB! if you have more than one form field, then it's especially
    // important to perform first all the validations and then check
    // the results. Otherwise all error messages will not be displayed
    // to the user.
    $phone_nr->Validate();
    $select->Validate();
    $date->Validate();
    
    echo $date->getValue();

    // if field is filled correctly, then display message and exit
    // otherwise continue and redisplay the form
    if ( $phone_nr->IsValid() && $select->IsValid() && $date->IsValid() )
    {
        echo "Success!!!";
        exit;
    }
}

// get the phone number field as <INPUT TYPE="TEXT"...
$phone_nr_input=$phone_nr->getFormControl();
$phone_nr_error=$phone_nr->getErrorMessage();
$select_input=$select->getFormControl();
$select_error=$select->getErrorMessage();
$date_input=$date->getFormControl();
$date_error=$date->getErrorMessage();

echo <<<EOHTML
<html>
<head>
<title>Test</title>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
</head>
<body>
<!-- it's important to post the form back to the same page that issued it -->
<form action="" method="post">

<p><label>Phone Number:</label> $phone_nr_input $phone_nr_error</p>

<p><label>Year:</label> $select_input $select_error</p>

<p>$date_input[day] $date_input[month] $date_input[year] $date_error</p>

<!-- Note, that the submit-button has name="submit", which we used before
in an if-clause to identify if the form has been posted back -->
<p><input type="submit" name="submit" value="Submit!" /></p>
</form>
</body>
</html>
EOHTML;

?>