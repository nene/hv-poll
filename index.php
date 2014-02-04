<?php

require_once("selectfields.php");
require_once("mysqlconnect.php");

/**
 * A validator for checking if the value looks like age.
 *
 */
class NumericRangeValidator extends Validator
{
    
    var $mMin=0;
    var $mMax=100;
    
    /**
     * Constructor
     *
     * @param mixed errorMessage
     * See class Validator for details.
     */
    function NumericRangeValidator( $errorMessage="Number out of range.", $min=0, $max=100 )
    {
        if ($min > $max)
        {
            trigger_error("NumericRangeValidator->NumericRangeValidator: parameter \$min larger than \$max.");
        }
        
        $this->mMin=$min;
        $this->mMax=$max;
        
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
        // convert text to number
        $number = (int)$text;
        
        // if number is between min and max, return true, otherwise false
        return ($number >= $this->mMin && $number <= $this->mMax);
    }
}


function form_is_submitted()
{
    return isset($_POST['submit']);
}

function all_form_fields_are_valid($form_fields)
{
    // loop through all fields
    foreach ($form_fields as $field)
    {
        // if one of them is invalid, return false
        if (!$field->IsValid()) return false;
    }
    // if all are valid, return true
    return true;
}

function create_mysql_create_query($form_fields)
{
    // start with INSERT INTO...
    $query = "CREATE TABLE hv_poll (\n";
    $query.= "`id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT\n";

    // loop through all fields
    foreach ($form_fields as $key => $field)
    {
        $query .= ",";

        $insert_data = mysql_real_escape_string($field->getValue());
        if ($key=='veel')
        {
            $query .= "`$key` TEXT NOT NULL\n";
        }
        elseif (preg_match('/_legal|_pirate|vanus|Arvutimang|(Suur|Vaike)Tarkvara/', $key) )
        {
            $query .= "`$key` INT NOT NULL DEFAULT '0'\n";
        }
        elseif ($key == 'sugu')
        {
            $query .= "`$key` ENUM('Mees', 'Naine') NOT NULL\n";
        }
        else
        {
            $query .= "`$key` ENUM('on', 'off') NOT NULL DEFAULT 'off'\n";
        }

    }

    // end the query
    $query .= ") CHARACTER SET utf8 COLLATE utf8_estonian_ci;";

    return $query;
}

function create_mysql_insert_query($form_fields)
{
    // start with INSERT INTO...
    $query = "INSERT INTO hv_poll VALUES (\n";
    $query.= "''\n";

    // loop through all fields
    foreach ($form_fields as $key => $field)
    {
        $query .= ",";

        $insert_data = mysql_real_escape_string($field->getValue());
        $query .= "'$insert_data' #$key\n";
    }

    // end the query
    $query .= ");";

    return $query;
}


// define all form fields
$form_fields['vanus'] = new TextField('vanus');
$form_fields['vanus']->AddValidator( new RequiredFieldValidator( "Vanuse sisestamine on kohustuslik." ) );
$form_fields['vanus']->AddValidator( new RegExValidator("Vanuse väli tohib sisaldada vaid numbrimärke.", '/^[0-9]+$/') );
$form_fields['vanus']->AddValidator( new NumericRangeValidator("Vanus peab olema 10 ja 90 vahel. Sorry, vanaätid ja väiksed lapsed.", 10, 90) );

$sood=Array('Mees'=>'Mees', 'Naine'=>'Naine');
$form_fields['sugu'] = new SelectField('sugu', $sood);
$form_fields['sugu']->AddValidator( new RequiredFieldValidator( "Oma soo sisestamine on kohustuslik." ) );
$form_fields['sugu']->AddValidator( new RangeValidator( "Sugu peab olema kas mees või naine.", $sood) );

$form_fields['it_inimene'] = new CheckBoxField('it_inimene');

$software_category = Array(
    'Arvutimang' => 'Arvutimängu',
    'SuurTarkvara' => 'Suuremat tarkvarapaketti (Office, Pilditöötlus, CAD, Statistika, Raamatupidamine...)',
    'VaikeTarkvara' => "Väiksemat rakendusprogrammi (a'la WinZip, EditPlus)",
);

foreach ($software_category as $abbr => $title)
{
    $form_fields[$abbr] = new TextField($abbr);
    $form_fields[$abbr]->AddValidator( new RegExValidator("See väli tohib sisaldada vaid numbrimärke.", '/^[0-9]*$/') );
    $form_fields[$abbr]->AddValidator( new NumericRangeValidator("Selle välja väärtus peab olema nulli ja miljoni vahel", 0, 1000000) );
}


$os = Array(
    "Windows2003" => "Windows 2003",
    "WindowsXP" => "Windows XP",
    "WindowsMe" => "Windows Me",
    "Windows2000" => "Windows 2000",
    "WindowsNT" => "Windows NT",
    "Windows98" => "Windows 98",
    "Windows95" => "Windows 95",
    "MacOSX" => "Mac OS (X)",
    "Linux" => "Linux",
    "BSD" => "BSD",
);
foreach ($os as $abbr => $title)
{
    $form_fields[$abbr."_legal"] = new TextField($abbr."_legal");
    $form_fields[$abbr."_legal"]->AddValidator( new RegExValidator("Legaalset kraami puudutav väli tohib sisaldada vaid numbrimärke.", '/^[0-9]*$/') );
    $form_fields[$abbr."_legal"]->AddValidator( new NumericRangeValidator("Legaalset kraami puudutava välja väärtus peab olema nulli ja miljoni vahel", 0, 1000000) );

    $form_fields[$abbr."_pirate"] = new TextField($abbr."_pirate");
    $form_fields[$abbr."_pirate"]->AddValidator( new RegExValidator("Piraat-kraami puudutav väli tohib sisaldada vaid numbrimärke.", '/^[0-9]*$/') );
    $form_fields[$abbr."_pirate"]->AddValidator( new NumericRangeValidator("Piraat-kraami puudutava välja väärtus peab olema nulli ja miljoni vahel", 0, 1000000) );
}


$paketid = Array(
    "3DSMax" => "3DSMax",
    "Acrobat" => "Adobe Acrobat (mitte Acrobat Reader!)",
    "Audition" => "Adobe Audition",
    "Illustrator" => "Adobe Illustrator",
    "Photoshop" => "Adobe Photoshop",
    "Premiere" => "Adobe Premiere",
    "AutoCAD" => "AutoCAD",
    "CppBuilder" => "Borland C++ Builder",
    "Delphi" => "Borland Delphi",
    "Bryce3D" => "Bryce3D",
    "CorelDraw" => "Corel Draw",
    "LightWave" => "LightWave",
    "Dreamweaver" => "Macromedia Dreamweaver",
    "Flash" => "Macromedia Flash",
    "Freehand" => "Macromedia Freehand",
    "Maya" => "Maya",
    "MSOffice" => "Microsoft Office",
    "VisualStudio" => "Microsoft Visual Studio",
    "Nero" => "Nero (plaadipõletustarkvara)",
    "NOD32" => "NOD32",
    "NortonAntivirus" => "Norton Antivirus",
    "Poser" => "Poser",
    "WinRar" => "WinRar",
    "WinZip" => "WinZip",
);
foreach ($paketid as $abbr=>$pakett)
{
    $form_fields[$abbr] = new CheckBoxField($abbr);
}

$form_fields['veel'] = new MultiLineField('veel', 8);



if ( !(isset($_COOKIE['HV-piraatluse-kysitlus']) &&
       $_COOKIE['HV-piraatluse-kysitlus']=='version-1.0') )
{
    // If "submit"-button was pressed
    if ( form_is_submitted() )
    {
        // Validate all form fields
        foreach ($form_fields as $key => $field)
        {
            $form_fields[$key]->Validate();
        }

        // if all fields are filled in correctly
        if ( all_form_fields_are_valid($form_fields) )
        {
                // update database
                mysql_query( create_mysql_insert_query($form_fields) );

                // set cookie to expire in 3 months
                setcookie( "HV-piraatluse-kysitlus", "version-1.0", time()+60*60*24*30*3 );

                // Redirect to 'thank you' page
                include("thank_you.php");
                exit();
        }
        else
        {
            $error_notice='<p class="error"><strong>Paistab, et sul jäid mõned väljad korrektselt täitmata. '.
            'Vaata allolev vorm üle - vigaselt täidetud väljade juurest leiad vastava märke.</strong></p>';
        }
    }
    else
    {
        $error_notice='';
    }
}
else
{
    $error_notice='<p class="error"><strong>Paistab, et oled sellele küsitlusele juba vastanud. '.
                    'Üle ühe korra vastata pole ilus.</strong></p>';
}

// Generate HTML for all form fields
foreach ($form_fields as $key => $field)
{
    $html[$key] = $field->getFormControl();
    if (strlen($field->getErrorMessage())>0)
    {
        $error[$key] = '<p class="error"><strong>'.$field->getErrorMessage().'</strong></p>';
    }
    else
    {
        $error[$key] = '';
    }
}



// Software category
$html_software_category = "<ul>\n";
foreach ($software_category as $abbr => $title)
{
    $html_software_category .= "<li><p><label>".$html[$abbr]." ".$title."</label></p>".$error[$abbr]."</li>\n";
}
$html_software_category .= "</ul>\n";

// OS
$html_os = "<ul>\n";
foreach ($os as $abbr => $title)
{
    $html_os .= "<li><p><label>".$html[$abbr."_legal"]." legaalset,</label> ".
                          "<label>".$html[$abbr."_pirate"]." mitte-legaalset</label> $title</p>".
                          $error[$abbr."_legal"].$error[$abbr."_pirate"]."</li>\n";
}
$html_os .= "</ul>\n";

// paketid
$html_paketid = "<ul>\n";
foreach ($paketid as $abbr => $pakett)
{
    $html_paketid .= "<li><label>".$html[$abbr]." $pakett</label></li>\n";
}
$html_paketid .= "</ul>\n";



// Output HTML
echo <<<EOHTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>HV Foorumi küsitlus piraatluse kohta</title>
<link type="text/css" rel="stylesheet" media="screen,projection" href="screen.css" />
</head>
<body>

<h1><a href="http://foorum.hv.ee">HV Foorumi</a> küsitlus piraatluse kohta</h1>

<p>See on küsitlusele vastamise vorm. Kui kardad oma isiklikke andmeid siia kirjutada,
siis tea, et <strong>vastajate anonüümsus on igati tagatud</strong>. Kui sind huvitavad
küsitluse tulemused, siis vaata <a href="statistics.php">tulemuste lehele</a>, kus
leidub mõningat statistikat.</p>

<!-- p><strong>NB! Momendil on see küsitlus alles testimisel.</strong></p -->

$error_notice

<form action="" method="post">

<h2>Üldandmed:</h2>

<p class="text"><label>Vanus: $html[vanus]</label></p>
$error[vanus]

<p><label>Sugu: $html[sugu]</label></p>
$error[sugu]

<p><label>$html[it_inimene] Pean ennast IT-inimeseks (näitaks töötan/õpin sellel alal või
lihtsalt hingega selle asja juures).</label></p>
$error[it_inimene]

<div class="text">
<h2>Minu arvuti(te)s on nii mitu <strong>mitte-legaalset</strong>
<span class="small">(märgi kastidesse kui palju sul üht või teist sorti
<em>piraattarkvara</em> on; kui täpselt ei tea, siis vähemasti hinnanguliselt):</span></h2>
$html_software_category
</div>

<div class="text">
<h2>Operatsioonisüsteemidest on mul <strong>legaalselt</strong> / <strong>mitte-legaalselt</strong>
<span class="small">(märgi esimesse kasti <em>legaalsete</em> OS-de arv,
teise <em>piraat</em> OS-de arv; kui täpselt ei tea, siis vähemasti hinnanguliselt):</span></h2>
$html_os
</div>

<h2>Tuntumatest tarkvarapakettidest on mul <strong>mitte-legaalselt</strong>:</h2>
$html_paketid

<fieldset>
<legend>Veel <strong>mitte-legaalseid</strong> tarkvarapakette minu arvuti(te)s:</legend>
<p>$html[veel]</p>
$error[veel]
</fieldset>

<p class="submit"><input type="submit" value="Saada vastused" name="submit" /></p>
</form>

</body>
</html>
EOHTML;

?>
