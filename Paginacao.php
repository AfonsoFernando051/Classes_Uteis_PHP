<?php

class Paginacao{

    private $total_registros;
    private $total_pagina;
  
    function getTotalRegistros(){
       return $this->total_registros;
    }
    function setTotalRegistros($total_registros){
        $this->total_registros = $total_registros;
    }
    function getTotalPaginaNavegacao(){
        return $this->total_pagina;
     }
     function setTotalPaginaNavegacao($total_registros, $registrosPorPagina){
        $this->total_pagina = ceil($total_registros / $registrosPorPagina);
     }
    
     /**
    * A classe Paginacao tem o método estático paginar que:
    * Recebe por parâmetro:
    * @param mixed $paginaAtual a página atual;
    * @param mixed $registrosPorPagina o número de registros por pág;
    * @param mixed $query a query principal;
    * @param mixed $params os parâmetros da query principal;
    * @param mixed $url a $url;
    * @param mixed $filtros e os filtros que montam a url;
    * É instanciado um objeto chamado dados_paginacao que seta com funções auxiliares o total de linhas retornadas pela query no atributo privado $total_registros
    * e a quantidade de páginas disponíveis para navegação no atributo $total_pagina.
    * Caso o atributo $total_pagina tenha valor = 1, não existirá o <nav> para vários registros, do contrário, é montada com a função gerarPaginacao(), um menu de navegação.
    */
    static function paginar($paginaAtual, $registrosPorPagina, $query, $params, $url, $filtros){

        $dados_paginacao = new self();
        $dados_paginacao->setTotalRegistros($dados_paginacao->queryTotaldeLinhas($query, $params));
        $dados_paginacao->setTotalPaginaNavegacao($dados_paginacao->getTotalRegistros(), $registrosPorPagina);
        $offset = ($paginaAtual - 1) * $registrosPorPagina;

        $paginacao = ($dados_paginacao->getTotalPaginaNavegacao() > 1)? $dados_paginacao->gerarPaginacao($dados_paginacao->getTotalPaginaNavegacao(), $paginaAtual, $url, $filtros) : NULL;

        return array(
            'concat_query' => $query . " OFFSET $offset ROWS FETCH NEXT $registrosPorPagina ROWS ONLY",
            'paginacao' => $paginacao,
        );
    }

    /*Função auxiliar que conta e retorna a quantidade de linhas da query principal*/
   public function queryTotaldeLinhas($query, $params){

        function removerOrderBy($query) {
            // Padrão para encontrar a cláusula ORDER BY em uma consulta
            $padrao = '/\s+ORDER\s+BY\s+.+/i';
            
            // Remove a cláusula ORDER BY usando uma string vazia
            $querySemOrderBy = preg_replace($padrao, '', $query);
            
            return $querySemOrderBy;
        }

        $queryPrincipal = removerOrderBy($query);

        global $conn;
       
        $query = " SELECT COUNT(*) AS total_registros FROM ( $queryPrincipal ) AS consulta";

        $result = sqlsrv_query($conn, $query, $params, array('Scrollable' => SQLSRV_CURSOR_FORWARD)) or die("Falha ao consultar dados no banco de dados");

        return sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)['total_registros'];
    
    }

    /*Função que retorna a o menu de paginção com os links corretos.*/
    private function gerarPaginacao($total_pagina, $pagina, $url, $filtros){
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