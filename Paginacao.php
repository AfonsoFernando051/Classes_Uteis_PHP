<?php

class Paginacao{

    private static $total_registros;
    private static $total_pagina;
  
    static function getTotalRegistros(){
       return self::$total_registros;
    }
    static function setTotalRegistros($total_registros){
        self::$total_registros = $total_registros;
    }
    static function getTotalPaginaNavegacao(){
        return self::$total_pagina;
    }
    static function setTotalPaginaNavegacao($total_registros, $registrosPorPagina){
        self::$total_pagina = ceil($total_registros / $registrosPorPagina);
    }
    
     /**
    * A classe Paginacao tem o método estático paginar que:
    * Recebe por parâmetro:
    * @param mixed $paginaAtual a página atual;
    * @param mixed $registrosPorPagina o número de registros por pág;
    * @param mixed $query a query principal sem o order by para contagem;
    * @param mixed $sql_orderby a query principal com order by para retornar função OFFSET;
    * @param mixed $params os parâmetros da query principal;
    * @param mixed $url a $url;
    * @param mixed $filtros e os filtros que montam a url;
    * É setado com funções auxiliares o total de linhas retornadas pela query no atributo privado $total_registros
    * e a quantidade de páginas disponíveis para navegação no atributo $total_pagina.
    * Caso o atributo $total_pagina tenha valor = 1, não existirá o <nav> para vários registros, do contrário, é montada com a função gerarPaginacao(), um menu de navegação.
    * A função paginar retorna um array com valores que possuem:
    * A query principal com a função de offset - 'concat_query';
    * O HTML com os hrefs para paginação - 'paginacao';
    * O total de registros para impressão na tela = 'total_de_registros';
    * O offset que caracterizará a orderm das linhas - 'offset'.
    */
    static function paginar($paginaAtual, $registrosPorPagina, $query, $sql_orderby, $params, $url, $filtros){

        self::$total_registros = self::queryTotaldeLinhas($query, $params);
        self::setTotalPaginaNavegacao(self::$total_registros, $registrosPorPagina);
        $offset = ($paginaAtual - 1) * $registrosPorPagina;

        $paginacao = (self::$total_pagina > 1)? self::gerarPaginacao(self::getTotalPaginaNavegacao(), $paginaAtual, $url, $filtros) : NULL;

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

        $result = sqlsrv_query($conn, $query, $params, array('Scrollable' => SQLSRV_CURSOR_FORWARD)) or die("Falha ao consultar dados no banco de dados");

        return sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)['total_registros'];
    
    }

    /*Função que retorna a o menu de paginção com os links corretos.*/
    private static function gerarPaginacao($total_pagina, $pagina, $url, $filtros){
        // $filtros_url = http_build_query($filtros);

        $anterior     = ($pagina > 1)? "{$url}?{$filtros}&pagina=".($pagina-1): "#";
        $anterior_dis = ($pagina <= 1)? " disabled ": "";
        $pagination = "<nav aria-label='...' style='margin-bottom: 20px;'>
                        <ul class='pagination justify-content-center'>
                            <li class='page-item {$anterior_dis}'>
                                <a class='page-link' href='{$anterior}' tabindex='-1'>Anterior</a>
                            </li> ";
                            for ($i=1; $i <=$total_pagina; $i++) {
                                $active  = ($i == $pagina)? 'active': "";
                                $pagination .="
                                    <li class='page-item {$active}'>
                                        <a class='page-link' href='{$url}?{$filtros}&pagina={$i}'>{$i}</a>
                                    </li>
                                    ";
                            }
            $proxima     = ($total_pagina > $pagina)? "{$url}?{$filtros}&pagina=".($pagina+1): "#";
            $proxima_dis = ($total_pagina <= $pagina)? " disabled ": "";
            $pagination .= "<li class='page-item {$proxima_dis}'>
                            <a class='page-link' href='{$proxima}'>Próximo</a>
                            </li>
                        </ul>
                    </nav>";
        return $pagination;
    }

}