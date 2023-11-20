<?php
/**
 * Classe para validação de endereços de e-mail.
 */
class ValidaEmail{
    /**
     * Valida um endereço de e-mail.
     *
     * @param string $email O endereço de e-mail a ser validado.
     *
     * @return bool Retorna true se o e-mail for válido, caso contrário, retorna false.
     */
    static function ValidacaoDeEmail($email){
        // Normaliza o e-mail para minúsculas
        $email = strtolower($email);

        // Obtém as últimas substrings para verificação posterior
        $ultimaStringPCom = substr($email, -4);
        $ultimaStringPComPBR = substr($email, -7);

        // Flag para indicar se o e-mail foi encontrado
        $encontrado = false;

        // Padrão para validação de e-mail
        $padrao = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

        // Verifica se o e-mail contém '@', não possui espaços em branco e atende ao padrão
        if(strpos($email, '@') && (strpos($email, ' ') == false) && preg_match($padrao, $email) ){
            $encontrado = true;
        }
        
        // Verifica se o e-mail foi encontrado e possui a extensão '.com' ou '.com.br' e valida com FILTER_VALIDATE_EMAIL
        if(($encontrado) && ($ultimaStringPCom === '.com' || $ultimaStringPComPBR === '.com.br') && filter_var($email, FILTER_VALIDATE_EMAIL)){
            return true;
        }else{
            return false;
        }
    }
}