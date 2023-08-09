<?php

class Paginacao{

    private static $total_registros;
    private static $total_de_paginas_navegacao;
  
    static function setTotalRegistros($total_registros){
        self::$total_registros = $total_registros;
    }
    static function setTotalPaginaNavegacao($total_registros, $registrosPorPagina){
        self::$total_de_paginas_navegacao = ceil($total_registros / $registrosPorPagina); // O total de páginas é calculado de acordo com o quociente do total de registros com a qtd de registros por página. 
    }
    
     /**
    * A classe Paginacao tem o método estático paginar que:
    * Recebe por parâmetro:
    * @param mixed $paginaAtual a página atual;
    * @param mixed $registrosPorPagina o número de registros por pág;
    * @param mixed $query a query principal sem o order by para contagem;
    * @param mixed $sql_orderby a query principal com order by para retornar função OFFSET;
    * @param mixed $params os parâmetros da query principal;

    * A função paginar retorna um array com valores que possuem:
    * A query principal com a função de offset - 'concat_query';
    * O HTML com os hrefs para paginação - 'paginacao';
    * O total de registros para impressão na tela = 'total_de_registros';
    * O offset que caracterizará a orderm das linhas - 'offset'.
    */
    static function paginar($paginaAtual, $registrosPorPagina, $query, $sql_orderby, $params){
        $url = $_SERVER['SCRIPT_NAME']; //Nome do arquivo que vêm na URL;
        $filtros = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY); //Parâmetros que vêm na URL;
    
        self::$total_registros = self::queryTotaldeLinhas($query, $params); // O atributo $total_registros recebe o total de registros da query;
        self::setTotalPaginaNavegacao(self::$total_registros, $registrosPorPagina); //O atributo $total_de_paginas_navegacao recebe a quantidade de páginas disponíveis para navegação.
        $offset = ($paginaAtual - 1) * $registrosPorPagina; // Offset são as linhas dos registros nas diferentes páginas.

        $paginacao = (self::$total_de_paginas_navegacao > 1)? self::gerarPaginacao(self::$total_de_paginas_navegacao, $paginaAtual, $url, $filtros) : NULL; //Função que retorna o HTML da paginação caso haja mais de uma página.

        return array(
            'concat_query' =>  "$sql_orderby OFFSET $offset ROWS FETCH NEXT $registrosPorPagina ROWS ONLY",
            'paginacao' => $paginacao,
            'total_de_registros' => self::$total_registros,
            'offset' => $offset
        );
    }

    /*Função auxiliar que conta e retorna a quantidade de linhas da query principal*/
    private static function queryTotaldeLinhas($query, $params){

        global $conn;
       
        $query = " SELECT COUNT(*) AS total_registros FROM ( $query ) AS consulta";

        $result = sqlsrv_query($conn, $query, $params, array('Scrollable' => SQLSRV_CURSOR_FORWARD));
        if(!$result){
            if( ($errors = sqlsrv_errors() ) != null) {
                error_log("Paginacao@".__FUNCTION__.$errors[0]['message']);
                return false;
            }
        }

        return sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)['total_registros'];
    
    }

    /*Função que retorna a o menu de paginção com os links corretos.
    O CSS utiliza algumas classes do Bootstrap 4.3 em algumas etapas.
    */
    private static function gerarPaginacao($total_de_paginas_navegacao, $pagina, $url, $filtros){

        $maxPagesToShow = 5;
        $firstPageToShow = max(1, $pagina - floor($maxPagesToShow / 2));
        $lastPageToShow = min($total_de_paginas_navegacao, $firstPageToShow + $maxPagesToShow - 1);
        $firstPageToShow = max(1, $lastPageToShow - $maxPagesToShow + 1);
        $pagination = "
            <style>
                ul.pagination li a {
                    text-decoration: none;
                    border-top: 1px solid #ddd;
                    border-bottom: 1px solid #ddd;
                    border-left: 1px solid #ddd;
                    border-right: 1px solid #ddd;
                }
            </style> 
        ";

        $pagination .= "<nav aria-label='...' style='margin-bottom: 20px;'>
                        <ul class='pagination justify-content-center' >";
        if($pagina > 1){
            $anterior = ($pagina > 1)? "{$url}?{$filtros}&pagina=".($pagina-1): "#";
            $pagination .= "<li class='page-item'>
                                <a class='page-link' href='{$anterior}'>Anterior</a>
                            </li>";
        }
        for ($page = $firstPageToShow; $page <= $lastPageToShow; $page++) {
            $active  = ($page == $pagina)? 'active': "";
            $pagination .= "<li class='page-item {$active}'>
                                <a class='page-link' href='{$url}?{$filtros}&pagina={$page}'>{$page}</a>
                            </li>";
        }
        if ($page < $total_de_paginas_navegacao) {
            $proxima = ($total_de_paginas_navegacao > $pagina)? "{$url}?{$filtros}&pagina=".($pagina+1): "#";
            $pagination .= "<li class='page-item {$active}'>
                                <a class='page-link' href='{$proxima}'>Próxima</a>
                            </li>";
        }
            $pagination .= "
                </ul>
                    </nav>";
        return $pagination;
    }

}