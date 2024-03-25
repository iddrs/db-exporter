<?php

namespace DbExporter\Db;

/**
 * Ferramentas de acesso e interação com o banco de dados
 *
 * @author Everton
 */
class Connection {
    
    private \PgSql\Connection $con;
    
    public function __construct(string $dsn) {
        $this->con = pg_connect($dsn);
    }
    
    public function query(string $query): \PgSql\Result {
        return pg_query(connection: $this->con, query: $query);
    }
    
    public function listSchemes(): array {
        return pg_fetch_all_columns($this->query("SELECT schema_name FROM information_schema.schemata WHERE schema_name !~ '^pg_' AND schema_name <> 'information_schema' AND schema_name <> 'public' AND schema_name <> 'tmp';"), 0);
    }
    
    public function listTables(string $schema): array {
        $tables = [];
        
        $exclude = ['mapeamento_cc'];//tabelas que não serão exportadas
        foreach(pg_fetch_all_columns($this->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '$schema';"), 0) as $t){
            if(in_array($t, $exclude)) continue;
            $tables[] = $t;
        }
        
        return $tables;
    }
    
    public function loadDataToExport(string $schema, string $table, string $remessa): array {
        return pg_fetch_all($this->query(sprintf("SELECT * FROM %s.%s WHERE remessa = %d", $schema, $table, $remessa)), PGSQL_ASSOC);
    }
}
