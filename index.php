<!DOCTYPE html>
<head>
<meta charset="utf-8">
<title>Domains Checker</title>
<link rel="stylesheet" href="styles.css" type="text/css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
<script src="script.js"></script>
</head>
<body>
<?php
    error_reporting( E_ALL ); //Включаем отображение ошибок
?>
<?php

require_once 'Net/Whois.php'; //Подключаем класс для получения информации о доменах
require_once('vendor/autoload.php'); //Подключаем composer

$date = date('d-m-Y');

//Получаем информацию о домене
function GetDomainInfo($q,$orig_name) { 
$server = 'whois.r01.ru'; //Whois server
$whois = new Net_Whois;
$data = $whois->query($q,$server);
//echo($data) . "<br>\n"; //Выводит ответ в сыром виде, можно включить для отладки
//Ниже вычленяем необходимую информацию о домене
$reg_till_label = 'reg-till:';
$created_label = 'created:';
$changed_label = 'changed:';
$free_date_label = 'free-date:';
$registrar_label = 'registrar:';
preg_match('/'.preg_quote($reg_till_label).'(.*?)'.preg_quote($created_label).'/is', $data, $reg_till);
preg_match('/'.preg_quote($created_label).'(.*?)'.preg_quote($changed_label).'/is', $data, $created);
preg_match('/'.preg_quote($changed_label).'(.*?)'.preg_quote($free_date_label).'/is', $data, $changed);
preg_match('/'.preg_quote($free_date_label).'(.*?)'.preg_quote($registrar_label).'/is', $data, $free_date);
$reg_till = $reg_till[1];
$created = $created[1];
$changed = $changed[1];
$free_date = $free_date[1];

//Если домен кириллический, передаем в таблицу его имя на кириллице
if ($orig_name) {
        FormatTableRow($orig_name, $created, $changed, $reg_till, $free_date);
    } else {
        FormatTableRow($q, $created, $changed, $reg_till, $free_date);
    }
}

//Функция формирует ряд таблицы
function FormatTableRow($domain_name, $created, $changed, $reg_till, $free_date) {
    $table_row = "<tr><td class=\"domain-name\">" . $domain_name . "</td><td class=\"created\">" . $created . "</td><td class=\"changed\">" .$changed . "</td><td class=\"reg-till\">" .$reg_till . "</td><td class=\"free-date\">" .$free_date . "</td></tr>";
    echo $table_row;
}

//Функция проверяет, состоит ли имя домена из латинских букв
function IsValidLatDomainName($domain_name)
{
    return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
            && preg_match("/^.{1,253}$/", $domain_name) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); //length of each label
}

echo "<div class=\"today\">Сегодня <span class=\"date\">" . $date . "</span></div>";

?>
<table class="domains">

<?php

FormatTableRow("<strong>Имя домена</strong>", "<strong>Created</strong>", "<strong>Changed</strong>", "<strong>Reg till</strong>", "<strong>Free date</strong>"); // Первый ряд таблицы с заголовками

$row = 1;
$handle = fopen("domains.csv", "r");
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data);
    $row++;
    for ($c=0; $c < $num; $c++) {
        if (IsValidLatDomainName($data[$c])) { //Если имя домена латинское
            GetDomainInfo($data[$c], false);
        } else {
            $idn = new \idna_convert(array('idn_version' => 2008)); //Если имя домена кириллическое
            GetDomainInfo($idn->encode($data[$c]),$data[$c]);
        }   
    }
}
fclose($handle);

?>
</table>
</body>