<?php

class Instante_Sniffs_ControlStructures_SwitchCaseHasBreakSniff implements PHP_CodeSniffer_Sniff
{
    public function register()
    {
        return [T_SWITCH];
    }

    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // We can't process SWITCH statements unless we know where they start and end.
        if (isset($tokens[$stackPtr]['scope_opener']) === FALSE
            || isset($tokens[$stackPtr]['scope_closer']) === FALSE
        ) {
            return;
        }

        $switch = $tokens[$stackPtr];
        $nextCase = $stackPtr;
        $nextBreak = $stackPtr;
        $cases = [];
        $breaks = [];

        while (($nextCase = $this->findNextCase($phpcsFile, ($nextCase + 1), $switch['scope_closer'])) !== FALSE) {
            $cases[] = $nextCase;
        }

        while (($nextBreak = $this->findNextBreak($phpcsFile, ($nextBreak + 1), $switch['scope_closer'])) !== FALSE) {
            $breaks[] = $nextBreak;
        }

        $casesCount = count($cases);

        for ($i = 0; $i < $casesCount; $i++) {
            $casePtr = $cases[$i];
            if (!isset($breaks[$i])) {
                if ($i !== ($casesCount - 1)) {
                    $phpcsFile->addError('Each case in switch statement must be terminated by BREAK except the last one', $casePtr);
                }
                break;
            }

            if ($breaks[$i] < $casePtr) {
                $phpcsFile->addError('Each case in switch statement must be terminated by BREAK except the last one', $casePtr);
                break;
            }
        }
    }

    private function findNextCase(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $end)
    {
        $tokens = $phpcsFile->getTokens();
        while (($stackPtr = $phpcsFile->findNext([T_CASE, T_DEFAULT, T_SWITCH], $stackPtr, $end)) !== FALSE) {
            // Skip nested SWITCH statements; they are handled on their own.
            if ($tokens[$stackPtr]['code'] === T_SWITCH) {
                $stackPtr = $tokens[$stackPtr]['scope_closer'];
                continue;
            }

            break;
        }

        return $stackPtr;
    }

    private function findNextBreak(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $end)
    {
        $tokens = $phpcsFile->getTokens();
        while (($stackPtr = $phpcsFile->findNext([T_BREAK, T_RETURN, T_SWITCH], $stackPtr, $end)) !== FALSE) {
            // Skip nested SWITCH statements; they are handled on their own.
            if ($tokens[$stackPtr]['code'] === T_SWITCH) {
                $stackPtr = $tokens[$stackPtr]['scope_closer'];
                continue;
            }

            break;
        }

        return $stackPtr;
    }

}