<?php

class Instante_Sniffs_NamingConventions_VariableIsCamelCaseSniff extends \PHP_CodeSniffer_Standards_AbstractVariableSniff
{
    protected function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $this->ensureCamelCase($phpcsFile, $stackPtr);
    }

    protected function processVariable(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $this->ensureCamelCase($phpcsFile, $stackPtr);
    }

    protected function processVariableInString(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $this->ensureCamelCase($phpcsFile, $stackPtr);
    }

    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int $stackPtr
     */
    private function ensureCamelCase(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $phpReservedVars = array(
            '_SERVER',
            '_GET',
            '_POST',
            '_REQUEST',
            '_SESSION',
            '_ENV',
            '_COOKIE',
            '_FILES',
            'GLOBALS',
            'http_response_header',
            'HTTP_RAW_POST_DATA',
            'php_errormsg',
        );

        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];
        $varName = ltrim($token['content'], '$');

        if (!$varName) {
            return;
        }

        if (in_array($varName, $phpReservedVars) === true) {
            return;
        }

        if ($varName[0] !== strtolower($varName[0])) {
            $phpcsFile->addError('Variable name "' . $token['content'] . '" must be in camelCase', $stackPtr);
        }
    }
}