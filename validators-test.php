<?php
require_once("validators.php");

echo "<h1>Tests for module 'validators'</h1>";

echo "<h2>class MinLengthValidator</h2>";

$values = Array(
    Array(1,"x",true),
    Array(2,"12345",true),
    Array(4,"foo",false),
    Array(8,"",false),
    Array(1,"",false),
);

echo "<ol>\n";

foreach ($values as $arr)
{
    echo "<li>";

    echo "minLength=$arr[0], ";
    echo "text=$arr[1], ";

    $v = new MinLengthValidator("", $arr[0]);
    $result = $v->Validate($arr[1]);

    echo "result=$result, ";

    if ($result == $arr[2])
    {
        echo "<span style='color:green'>OK</span>";
    }
    else
    {
        echo "<span style='color:red'>Error!</span>";
    }

    echo "</li>\n";
}

echo "</ol>\n";




echo "<h2>class MaxLengthValidator</h2>";

$values = Array(
    Array(1,"x",true),
    Array(2,"12345",false),
    Array(4,"foo",true),
    Array(100,"",true),
);

echo "<ol>\n";

foreach ($values as $arr)
{
    echo "<li>";

    echo "maxLength=$arr[0], ";
    echo "text=$arr[1], ";

    $v = new MaxLengthValidator("", $arr[0]);
    $result = $v->Validate($arr[1]);

    echo "result=$result, ";

    if ($result == $arr[2])
    {
        echo "<span style='color:green'>OK</span>";
    }
    else
    {
        echo "<span style='color:red'>Error!</span>";
    }

    echo "</li>\n";
}

echo "</ol>\n";






echo "<h2>class RegExValidator</h2>";

$values = Array(
    Array('/^.*$/', "foo", true),
    Array('/^o*$/', "foo", false),
    Array('/[XYZ]*/', "foo", true),
    Array('/^[XYZ]*$/', "foo", false),
    Array('/^(bar|foo|baz)$/', "foo", true),
    Array('//', "foo", true)
);

echo "<ol>\n";

foreach ($values as $arr)
{
    echo "<li>";

    echo "regEx=$arr[0], ";
    echo "text=$arr[1], ";

    $v = new RegExValidator("", $arr[0]);
    $result = $v->Validate($arr[1]);

    echo "result=$result, ";

    if ($result == $arr[2])
    {
        echo "<span style='color:green'>OK</span>";
    }
    else
    {
        echo "<span style='color:red'>Error!</span>";
    }

    echo "</li>\n";
}

echo "</ol>\n";


echo "<h2>class RequiredFieldValidator</h2>";

$values = Array(
    Array("foo", true),
    Array("", false),
    Array(" ", false),
    Array(" \t", false),
    Array(null, false),
);

echo "<ol>\n";

foreach ($values as $arr)
{
    echo "<li>";

    echo "text=$arr[0], ";

    $v = new RequiredFieldValidator("");
    $result = $v->Validate($arr[0]);

    echo "result=$result, ";

    if ($result == $arr[1])
    {
        echo "<span style='color:green'>OK</span>";
    }
    else
    {
        echo "<span style='color:red'>Error!</span>";
    }

    echo "</li>\n";
}

echo "</ol>\n";




echo "<h2>class EMailAddressValidator</h2>";

$values = Array(
    Array("foo", false),
    Array("", false),
    Array("nene@example.com.", true),
    Array("Foo@PHP.INFO", true),
    Array("zummu-jummu@jummu.pri.ee", true),
);

echo "<ol>\n";

foreach ($values as $arr)
{
    echo "<li>";

    echo "text=$arr[0], ";

    $v = new EMailAddressValidator("");
    $result = $v->Validate($arr[0]);

    echo "result=$result, ";

    if ($result == $arr[1])
    {
        echo "<span style='color:green'>OK</span>";
    }
    else
    {
        echo "<span style='color:red'>Error!</span>";
    }

    echo "</li>\n";
}

echo "</ol>\n";



echo "<h2>class UrlValidator</h2>";

$values = Array(
    Array("foo", false),
    Array("", false),
    Array("http://www.example.com.", true),
    Array("SFTP://EXAMPLE.COM/dir/file.html", true),
    Array("https://p.co.uk/li%20%20lo.la/w/t/f/", true),
);

echo "<ol>\n";

foreach ($values as $arr)
{
    echo "<li>";

    echo "text=$arr[0], ";

    $v = new UrlValidator("");
    $result = $v->Validate($arr[0]);

    echo "result=$result, ";

    if ($result == $arr[1])
    {
        echo "<span style='color:green'>OK</span>";
    }
    else
    {
        echo "<span style='color:red'>Error!</span>";
    }

    echo "</li>\n";
}

echo "</ol>\n";

?>