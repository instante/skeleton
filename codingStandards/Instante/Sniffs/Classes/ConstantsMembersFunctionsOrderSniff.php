<?php

class Instante_Sniffs_Classes_ConstantsMembersFunctionsOrderSniff extends PEAR_Sniffs_Classes_ClassDeclarationSniff
{

    public function register()
    {
        return [T_CLASS];
    }

    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $propertyEncountered = FALSE;
        $methodEncountered = FALSE;

        $tokens = $phpcsFile->getTokens();
        for ($i = min($stackPtr + 1, count($tokens)); $i < count($tokens); $i++) {
            $token = $tokens[$i];
            if ($this->isConstant($token)) {
                if ($propertyEncountered || $methodEncountered) {
                    $phpcsFile->addError('Constant "' . $tokens[$i + 2]['content'] . '" must be declared before any property or method', $stackPtr);
                }
            }

            if ($this->isProperty($token, $tokens, $i)) {
                $propertyEncountered = TRUE;
                if ($methodEncountered) {
                    $phpcsFile->addError('Property "' . $tokens[$i + 2]['content'] . '" must be declared before any method', $stackPtr);
                }
            }

            if ($this->isMethod($token, $tokens, $i)) {
                $methodEncountered = TRUE;
            }

            if ($this->isClass($token)) {
                return; // we want to check only current class if case we have more classes in one file
            }
        }
    }

    private function isConstant($token)
    {
        return $token['code'] === T_CONST;
    }

    private function isProperty($token, array $tokens, $stackPtr)
    {
        $tokenIsScopeModifier = in_array($token['code'], PHP_CodeSniffer_Tokens::$scopeModifiers);

        if (!$tokenIsScopeModifier) {
            return FALSE;
        }

        if (isset($tokens[$stackPtr + 1]) && isset($tokens[$stackPtr + 2])) {
            $followedByWhitespace = $tokens[$stackPtr + 1]['code'] === T_WHITESPACE;
            $followedByVar = $tokens[$stackPtr + 2]['code'] === T_VARIABLE;
            return $tokenIsScopeModifier && $followedByWhitespace && $followedByVar;

        } else {
            return FALSE;
        }
    }

    private function isMethod($token, array $tokens, $stackPtr)
    {
        $tokenIsFunction = $token['code'] === T_FUNCTION;
        if (isset($tokens[$stackPtr + 1]) && isset($tokens[$stackPtr + 2])) {
            $followedByWhitespaceAndString = $tokens[$stackPtr + 1]['code'] === T_WHITESPACE && $tokens[$stackPtr + 2]['code'] = T_STRING;
            return $tokenIsFunction && $followedByWhitespaceAndString;

        } else {
            return FALSE;
        }
    }

    private function isClass($token)
    {
        return $token['code'] === T_CLASS;
    }


}