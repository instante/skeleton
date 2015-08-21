<?php

class Instante_Sniffs_Operators_UnaryOperatorSpacingSniff implements PHP_CodeSniffer_Sniff
{
    public function register()
    {
        return [T_BOOLEAN_NOT];
    }

    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // TODO add fixable error
        if ($tokens[$stackPtr + 1]['code'] === T_WHITESPACE) {
            $spacesCount = strlen($tokens[$stackPtr + 1]['content']);
            $phpcsFile->addError('Expected 0 spaces after unary operator; %s found', $stackPtr, '', [$spacesCount]);
        }
    }
}