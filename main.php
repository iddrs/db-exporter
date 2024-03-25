<?php

require_once 'vendor/autoload.php';

$outputDir = 'C:\\Users\\Everton\\Desktop\\Dados Exportados';

$con = new DbExporter\Db\Connection(dsn: 'host=localhost port=5432 dbname=pmidd user=postgres password=lise890');


echo '================================================================================', PHP_EOL;
echo 'EXPORTAÇÃO DE DADOS DO POSTGRESQL PARA OUTROS FORMATOS', PHP_EOL;
echo '================================================================================', PHP_EOL;

echo PHP_EOL;
echo PHP_EOL;

echo '================================================================================', PHP_EOL;
echo 'Seleção de Schema:', PHP_EOL;
echo PHP_EOL;

$schemas = $con->listSchemes();

foreach ($schemas as $i => $s){
    printf("\t[%s ]\t%s".PHP_EOL, str_pad($i, 3, ' ', STR_PAD_LEFT), $s);
}

echo PHP_EOL;

echo 'Digite uma opção: ';
$option = (int) fgets(STDIN);

if(key_exists($option, $schemas)){
    $scheme = $schemas[$option];
    printf("-> Schema selecionado: %s".PHP_EOL, $scheme);
} else {
    trigger_error("Schema selecionado [$option] é inválido.", E_USER_ERROR);
}

echo PHP_EOL;
echo PHP_EOL;

echo '================================================================================', PHP_EOL;
echo 'Seleção da Remessa:', PHP_EOL;
echo PHP_EOL;

echo 'Digite o ano [AAAA]: ';
$ano = (string) trim(fgets(STDIN));
echo PHP_EOL;
echo 'Digite o mês[MM]: ';
$mes = str_pad(trim(fgets(STDIN)), 2, '0', STR_PAD_LEFT);

$remessa = "$ano$mes";
echo '================================================================================', PHP_EOL;
echo 'Exportação iniciada...', PHP_EOL;
echo PHP_EOL;

$outputFile = sprintf('%s\%s\%s-%s.xlsx', $outputDir, $scheme, $ano, $mes);
$exporter = new DbExporter\Export\ExportExcel($con, $scheme, $remessa, $outputFile);
$exporter->export();

echo 'Processo concluído', PHP_EOL;
printf('Dados salvos em %s'.PHP_EOL, $outputFile);
echo '================================================================================', PHP_EOL;