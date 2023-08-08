<?php
/**
 * Esta é uma classe feita para validar emails.
 */
class ValidaEmail{
    /**
    * A classe estática ValidacaoDeEmail recebe o email por parâmetro e verifica:
    * Se ele tem um @;
    * Se não tem espaço vazio;
    * E se o email que vem por parâmetro segue o padrão estabelecido.
    * Caso isso aconteça, a variável $encontrado recebe o valor true.
    * Depois, é verificado se o final termina com '.com' ou '.com.br', caso $encontrado seja true, e estas últimas validações sejam verdadeiras,
    * o método retorna true confirmando que o email é valido, caso contrário, exibe um alert do javascript e retorna false;
    *@param mixed $email - string email que será validada.
     */
    static function ValidacaoDeEmail($email){
        $email = strtolower($email);
        $ultimaStringPCom = substr($email, -4);
        $ultimaStringPComPBR = substr($email, -7);
        $encontrado = false;
        $padrao = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

        if(strpos($email, '@') && (strpos($email, ' ') == false) && preg_match($padrao, $email) ){
            $encontrado = true;
        }
        
        if(($encontrado) && ($ultimaStringPCom === '.com' || $ultimaStringPComPBR === '.com.br') && filter_var($email, FILTER_VALIDATE_EMAIL)){
            return true;
        }else{
            ?><script>alert("O email está vazio/incorreto, preencha um email válido por gentileza.\nVerifique:\n  Se foi digitado um espaço vazio entre o email;\n  Se há caracteres especiais.\n")</script><?php
            return false;
        }
    }
}