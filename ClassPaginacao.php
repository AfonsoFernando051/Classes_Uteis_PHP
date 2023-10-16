<?php

class Paginacao{
    private static $total_registros;
    private static $total_de_paginas_navegacao;
    static function setTotalRegistros($total_registros){
        self::$total_registros = $total_registros;
    }
    static function setTotalPaginaNavegacao($total_registros, $registrosPorPagina){
        self::$total_de_paginas_navegacao = ceil($total_registros / $registrosPorPagina);
    }
    
    static function paginar($paginaAtual, $registrosPorPagina, $query, $sql_orderby, $params){
        $url = $_SERVER['SCRIPT_NAME'];
        $filtros = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
       
        self::$total_registros = self::queryTotaldeLinhas($query, $params); 
        self::setTotalPaginaNavegacao(self::$total_registros, $registrosPorPagina);
        $offset = ($paginaAtual - 1) * $registrosPorPagina;

        $paginacao = (self::$total_de_paginas_navegacao > 1)? self::gerarPaginacao(self::$total_de_paginas_navegacao, $paginaAtual, $url, $filtros) : NULL;

        return array(
            'concat_query' =>  "$sql_orderby OFFSET $offset ROWS FETCH NEXT $registrosPorPagina ROWS ONLY",
            'paginacao' => $paginacao,
            'total_de_registros' => self::$total_registros,
            'offset' => $offset
        );
    }

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
            $filtros = self::removeParamsFiltro($filtros, $pagina);
            $anterior = ($pagina > 1)? "{$url}?{$filtros}&pagina=".($pagina-1): "#";
            $primeira = ($pagina > 1)? "{$url}?{$filtros}&pagina=".(1): "#";
            $pagination .= "<li class='page-item'>
                                <a class='page-link' href='{$primeira}'>Primeira</a>
                            </li>";
            $pagination .= "<li class='page-item'>
                                <a class='page-link' href='{$anterior}'>&laquo</a>
                            </li>";
        }
        for ($page = $firstPageToShow; $page <= $lastPageToShow; $page++) {
            $filtros = self::removeParamsFiltro($filtros, $pagina);
            $active  = ($page == $pagina)? 'active': "";
            $pagination .= "<li class='page-item {$active}'>
                                <a class='page-link' href='{$url}?{$filtros}&pagina={$page}'>{$page}</a>
                            </li>";
        }
        if ($page < $total_de_paginas_navegacao) {
            $filtros = self::removeParamsFiltro($filtros, $pagina);
            $proxima = ($total_de_paginas_navegacao > $pagina)? "{$url}?{$filtros}&pagina=".($pagina+1): "#";
            $ultima = ($total_de_paginas_navegacao > $pagina)? "{$url}?{$filtros}&pagina=".($total_de_paginas_navegacao): "#";
            $pagination .= "<li class='page-item {$active}'>
                                <a class='page-link' href='{$proxima}'>&raquo;</a>
                            </li>";
            $pagination .= "<li class='page-item {$active}'>
                                <a class='page-link' href='{$ultima}'>Última</a>
                            </li>";         
        }
            $pagination .= "
                </ul>
                    </nav>";
        return $pagination;
    }

    private static function removeParamsFiltro($filtro, $pagina){
        $substring = "&pagina=$pagina";
        $filtro = str_replace($substring, '', $filtro);
        return $filtro;
    }
}
