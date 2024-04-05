<?php

namespace DbExporter\Export;

use DbExporter\Db\Connection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Exporta do banco de dados para o Excel
 *
 * @author Everton
 */
class ExportExcel {
    
    private Connection $con;
    
    private string $scheme;
    
    private string $remessa;
    
    private readonly string $outputFile;
    
    private Spreadsheet $spreadsheet;

    public function __construct(Connection $con, string $scheme, string $remessa, string $outputFile) {
        printf("Scheme:\t%s".PHP_EOL, $scheme);
        printf("Remessa:\t%s".PHP_EOL, $remessa);
        printf("Destino:\t%s".PHP_EOL, $outputFile);
        
        $this->con = $con;
        $this->scheme = $scheme;
        $this->remessa = $remessa;
        $this->outputFile = $outputFile;
        $this->spreadsheet = new Spreadsheet();
        $this->clearOutputFile();
    }
    
    private function clearOutputFile(): void {
        if(file_exists($this->outputFile)){
            printf('Excluindo %s'.PHP_EOL, $this->outputFile);
            unlink($this->outputFile);
        }
    }
    
    public function export(): void {
        $tables = $this->con->listTables($this->scheme);
        print_r($tables);
        $sheetIndex = 0;
        foreach ($tables as $table){
            
            printf('Exportando dados de %s'.PHP_EOL, $table);
            $data = $this->con->loadDataToExport($this->scheme, $table, $this->remessa);
            if(!key_exists(0, $data)) continue;//se não tiver dados, pula a tabela.
            
            if($sheetIndex > 0) {
                $this->spreadsheet->createSheet($sheetIndex);
            }
            $this->spreadsheet->setActiveSheetIndex($sheetIndex);
            $sheetIndex++;
            
            
            
            $i = 1;
            $header = [];
            foreach ($data[0] as $label => $item){
                $header[$i] = $label;
                $this->spreadsheet->getActiveSheet()->setCellValue([$i, 1], $label);
                $i++;
            }
//            print_r($header);
            
            printf("Exportando %d linhas".PHP_EOL, sizeof($data));
            $this->spreadsheet->getActiveSheet()->fromArray(source: $data, startCell: 'A2');
            
            printf("Criando a tabela de dados %s".PHP_EOL, $table);
            $range = [
                1,//from column
                1,//from row
                sizeof($data[0]),//to column
                sizeof($data),//to row
            ];
            $xtable = new Table($range, $table);
            $tableStyle = new TableStyle();
            $tableStyle->setTheme(TableStyle::TABLE_STYLE_LIGHT1);
            $tableStyle->setShowRowStripes(true);
            $tableStyle->setShowColumnStripes(true);
            $tableStyle->setShowFirstColumn(false);
            $tableStyle->setShowLastColumn(false);
            $xtable->setStyle($tableStyle);
            $xtable->setAllowFilter(false);
            $this->spreadsheet->getActiveSheet()->addTable($xtable);
         
            printf("Renomenado a planilha para %s".PHP_EOL, $table);
            $this->spreadsheet->getActiveSheet()->setTitle($table);
            
        }
        printf("Salvando os dados em %s".PHP_EOL, $this->outputFile);
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($this->outputFile);
    }
}
