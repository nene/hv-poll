<?php

require_once("mysqlconnect.php");
error_reporting(E_ALL);

function plot_as_table_headings($headings)
{
    $html="";
    $cell_nr=0;
    foreach ($headings as $heading)
    {
        if ($cell_nr==0)
        {
            $html.="<th class='first-child'>$heading</th>";
        }
        else
        {
            $html.="<th>$heading</th>";
        }
        $cell_nr++;
    }
    return $html;
}

function plot_as_table_cells($cells)
{
    $html="";
    foreach ($cells as $cell)
    {
        // if value is numeric, add specific class
        if ( is_numeric($cell) )
        {
            $html.="<td class='numeric'>$cell</td>";
        }
        else
        {
            $html.="<td>$cell</td>";
        }
    }
    return $html;
}

function plot_as_table_rows($rows)
{
    $table="";
    $row_nr=0;
    foreach ($rows as $row)
    {
        $row_nr++;
        if ( $row_nr % 2 == 0)
        {
            $class="even";
        }
        else
        {
            $class="odd";
        }
        $table.="<tr class='$class'>".plot_as_table_cells($row)."</tr>\n";
    }
    return $table;
}

function create_table_header($caption, $column_headings)
{
    $table = "<table>\n";
    $table.= "<caption>$caption</caption>\n";
    $table.= "<tr>".plot_as_table_headings($column_headings)."</tr>\n";
    return $table;
}

function create_table_footer()
{
    return "</table>\n";
}

function plot_as_table($resultset, $caption, $headings)
{
    $rows = Array();
    while ($row = mysql_fetch_row($resultset))
    {
        $rows[] = $row;
    }

    $table = create_table_header($caption, $headings);
    $table.= plot_as_table_rows($rows);
    $table.= create_table_footer();

    return $table;
}

function plot_as_histogram_table($resultset, $caption, $headings, $group_upper_bounds, $groups)
{
    // loop through the results
    while ($row = mysql_fetch_row($resultset))
    {
        // go through all group upper bounds
        foreach ($group_upper_bounds as $index => $upper_bound)
        {
            // if value is smaller than upper bound, increment the group
            if ($row[0] <= $upper_bound)
            {
                if ( ! isset($rows[$index]) )
                {
                    $rows[$index][0]=$groups[$index];
                    $rows[$index][1]=0;
                }
                $rows[$index][1] += $row[1];
                break;
            }
        }
    }

    $table = create_table_header($caption, $headings);
    $table.= plot_as_table_rows($rows);
    $table.= create_table_footer();

    return $table;
}


function nr_of_rows_in_table()
{
    $result = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM hv_poll"));
    return $result[0];
}

function age_query()
{
    return mysql_query("SELECT
                          vanus,
                          COUNT(vanus)
                        FROM hv_poll
                        GROUP BY vanus
                        ORDER BY vanus");
}

function sex_query()
{
    return mysql_query("SELECT
                          sugu,
                          COUNT(sugu)
                        FROM hv_poll
                        GROUP BY sugu
                        ORDER BY sugu");
}

function it_query()
{
    return mysql_query("SELECT
                          CASE it_inimene
                            WHEN 'on' THEN 'Jah'
                            ELSE 'Ei'
                          END,
                          COUNT(it_inimene)
                        FROM hv_poll
                        GROUP BY it_inimene
                        ORDER BY it_inimene");
}

function game_query()
{
    return mysql_query("SELECT
                          Arvutimang,
                          COUNT(Arvutimang)
                        FROM hv_poll
                        GROUP BY Arvutimang
                        ORDER BY Arvutimang");
}

function big_software_query()
{
    return mysql_query("SELECT
                          SuurTarkvara,
                          COUNT(SuurTarkvara)
                        FROM hv_poll
                        GROUP BY SuurTarkvara
                        ORDER BY SuurTarkvara");
}

function small_software_query()
{
    return mysql_query("SELECT
                          VaikeTarkvara,
                          COUNT(VaikeTarkvara)
                        FROM hv_poll
                        GROUP BY VaikeTarkvara
                        ORDER BY VaikeTarkvara");
}

function os_query($os_abbr)
{
    return mysql_query("SELECT
                          COUNT($os_abbr)
                        FROM hv_poll
                        WHERE $os_abbr > 0");
}


function sw_packet_query($packet)
{
    return mysql_query("SELECT
                          COUNT($packet)
                        FROM hv_poll
                        WHERE $packet='on'");
}

function extra_pirate_sw()
{
    $result = mysql_query("SELECT
                          veel
                        FROM hv_poll");

    $even_or_odd = 'odd';
    $text = "<ul>\n";
    while ($veel = mysql_fetch_array($result))
    {
        // skip all empty entries
        if ($veel['veel'] != "")
        {
            // remove possibly malicious HTML
            $veel['veel'] = strip_tags($veel['veel']);
            // replace linebrakes with commas
            $veel['veel'] = preg_replace('/ *(\015\012|\015|\012) */', ', ', $veel['veel']);
            // append as HTML list item
            $text .= "<li class='$even_or_odd'>$veel[veel]</li>\n";

            if ($even_or_odd == 'even')
            {
                $even_or_odd = 'odd';
            }
            else
            {
                $even_or_odd = 'even';
            }
        }
    }
    $text .= "</ul>\n";
    return $text;
}

$vastanute_arv = nr_of_rows_in_table();


$vanus = plot_as_histogram_table(
                        age_query(),
                        'Vastanute vanuseline jaotus',
                        Array('Vanus', 'Selles vanuses vastajaid'),
                        Array(15, 20, 25, 30, 35, 40, 90),
                        Array('kuni 15', '16 .. 20', '21 .. 25', '26 .. 30',
                              '31 .. 35', '36 .. 40', 'üle 40')
                      );

$sugu  = plot_as_table( sex_query(),
                        'Vastanute sooline jaotus',
                        Array('Sugu', 'Sellest soost vastajaid')
                      );

$it_inimene=plot_as_table( it_query(),
                        'Kas pead ennast IT-inimeseks?',
                        Array('Vastus', 'Vastajate arv')
                      );

$mangud = plot_as_histogram_table(
                        game_query(),
                        'Mitte-legaalsete arvutimängude arv',
                        Array('Arvutimängude arv', 'Vastajate arv'),
                        Array(0, 3, 6, 10, 25, 50, 100, 1000000),
                        Array('null', '1 .. 3', '4 .. 6', '7 .. 10',
                              '11 .. 25', '26 .. 50', '51 .. 100', 'üle 100')
                      );

$suur_tarkvara=plot_as_histogram_table(
                        big_software_query(),
                        'Mitte-legaalsete suuremate tarkvarapakettide arv',
                        Array('Suuremate tarkvarapakettide arv', 'Vastajate arv'),
                        Array(0, 3, 6, 10, 25, 1000000),
                        Array('null', '1 .. 3', '4 .. 6', '7 .. 10',
                              '11 .. 25', 'üle 25')
                      );

$vaike_tarkvara=plot_as_histogram_table(
                        small_software_query(),
                        'Mitte-legaalsete väiksemate tarkvarapakettide arv',
                        Array('Väiksemate tarkvarapakettide arv', 'Vastajate arv'),
                        Array(0, 3, 6, 10, 25, 50, 1000000),
                        Array('null', '1 .. 3', '4 .. 6', '7 .. 10',
                              '11 .. 25', '26 .. 50', 'üle 50')
                      );



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
    $legal = os_query("{$abbr}_legal");
    $legal_row = mysql_fetch_array($legal);

    $pirate = os_query("{$abbr}_pirate");
    $pirate_row = mysql_fetch_array($pirate);

    $osid[] = Array($title, $legal_row[0], $pirate_row[0]);
}

$op_systeemid=create_table_header(
                        'Operatsioonisüsteemide jaotus',
                        Array('Operatsioonisüsteem',
                              'Kui paljud omavad legaalselt',
                              'Kui paljud mitte-legaalselt')
                      );
$op_systeemid.=plot_as_table_rows($osid);
$op_systeemid.=create_table_footer();







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

function cmp($a, $b)
{
    if ($a[1] > $b[1])
    {
        return -1;
    }
    elseif ($a[1] < $b[1])
    {
        return 1;
    }
    else
    {
        if ($a[0] > $b[0])
        {
            return 1;
        }
        elseif ($a[0] < $b[0])
        {
            return -1;
        }
        else
        {
            return 0;
        }
    }
}

$rows = Array();
foreach ($paketid as $paketi_id => $paketi_nimi)
{
      $resultset = sw_packet_query($paketi_id);
      $count = mysql_fetch_array($resultset);
    $rows[] = Array($paketi_nimi, $count[0]);
}
$tarkvara_paketid = create_table_header('Levinuimad piraat-tarkvarapaketid', 
                      Array('Tarkvara nimi', 'Vastajate arv')
                    );

usort($rows, "cmp");
$tarkvara_paketid.= plot_as_table_rows($rows);
$tarkvara_paketid.= create_table_footer();

$lisainfo = extra_pirate_sw();

// Output HTML
echo <<<EOHTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>HV Foorumi piraatluse küsitluse tulemused</title>
<link type="text/css" rel="stylesheet" media="screen,projection" href="screen.css" />
</head>
<body>

<h1><a href="http://foorum.hv.ee">HV Foorumi</a> piraatluse küsitluse tulemused</h1>

<p>Hetkeseisuga on küsitlusele vastanud $vastanute_arv inimest.
Kui sina pole seda veel teinud, siis on sul võimalus täita
<a href="index.php">HV foorumi piraatlusküsitluse vorm</a>.</p>

<h2>Üldandmed</h2>
$vanus
$sugu
$it_inimene

<h2>Mitte-legaalsete programmide arv vastajate arvutites</h2>
$mangud
$suur_tarkvara
$vaike_tarkvara

<h2>Operatsioonisüsteemidest legaalselt / mitte-legaalselt</h2>
$op_systeemid

<h2>Tuntumad mitte-legaalsed tarkvarapaketid vastajate arvutites</h2>
$tarkvara_paketid

<h2>Veel mitte-legaalseid tarkvarapakette vastanute arvutites</h2>

<p>$lisainfo</p>

</body>
</html>
EOHTML;

?>
