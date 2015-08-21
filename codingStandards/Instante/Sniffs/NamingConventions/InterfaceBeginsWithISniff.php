<?php

class Instante_Sniffs_NamingConventions_InterfaceBeginsWithISniff implements PHP_CodeSniffer_Sniff
{
    public function register()
    {
        return [T_INTERFACE];
    }

    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $interfaceNamePtr = $phpcsFile->findNext(T_STRING, $stackPtr);

        // TODO add fixable error
        $firstChar = $tokens[$interfaceNamePtr]['content'][0];
        $secondChar = $tokens[$interfaceNamePtr]['content'][1];
        if ($firstChar !== 'I' || strtoupper($secondChar) !== $secondChar) {
            $phpcsFile->addError('Interface must begin with I character', $stackPtr);
        }
    }
}