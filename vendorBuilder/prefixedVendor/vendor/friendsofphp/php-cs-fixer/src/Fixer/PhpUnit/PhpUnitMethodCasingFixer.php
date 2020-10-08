<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace MolliePrefix\PhpCsFixer\Fixer\PhpUnit;

use MolliePrefix\PhpCsFixer\AbstractFixer;
use MolliePrefix\PhpCsFixer\DocBlock\DocBlock;
use MolliePrefix\PhpCsFixer\DocBlock\Line;
use MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample;
use MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition;
use MolliePrefix\PhpCsFixer\Indicator\PhpUnitTestCaseIndicator;
use MolliePrefix\PhpCsFixer\Preg;
use MolliePrefix\PhpCsFixer\Tokenizer\Token;
use MolliePrefix\PhpCsFixer\Tokenizer\Tokens;
use MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer;
use MolliePrefix\PhpCsFixer\Utils;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 */
final class PhpUnitMethodCasingFixer extends \MolliePrefix\PhpCsFixer\AbstractFixer implements \MolliePrefix\PhpCsFixer\Fixer\ConfigurationDefinitionFixerInterface
{
    /**
     * @internal
     */
    const CAMEL_CASE = 'camel_case';
    /**
     * @internal
     */
    const SNAKE_CASE = 'snake_case';
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerDefinition\FixerDefinition('Enforce camel (or snake) case for PHPUnit test methods, following configuration.', [new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
class MyTest extends \\PhpUnit\\FrameWork\\TestCase
{
    public function test_my_code() {}
}
'), new \MolliePrefix\PhpCsFixer\FixerDefinition\CodeSample('<?php
class MyTest extends \\PhpUnit\\FrameWork\\TestCase
{
    public function testMyCode() {}
}
', ['case' => self::SNAKE_CASE])]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run after PhpUnitTestAnnotationFixer.
     */
    public function getPriority()
    {
        return 0;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_FUNCTION]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens)
    {
        $phpUnitTestCaseIndicator = new \MolliePrefix\PhpCsFixer\Indicator\PhpUnitTestCaseIndicator();
        foreach ($phpUnitTestCaseIndicator->findPhpUnitClasses($tokens) as $indexes) {
            $this->applyCasing($tokens, $indexes[0], $indexes[1]);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function createConfigurationDefinition()
    {
        return new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \MolliePrefix\PhpCsFixer\FixerConfiguration\FixerOptionBuilder('case', 'Apply camel or snake case to test methods'))->setAllowedValues([self::CAMEL_CASE, self::SNAKE_CASE])->setDefault(self::CAMEL_CASE)->getOption()]);
    }
    /**
     * @param int $startIndex
     * @param int $endIndex
     */
    private function applyCasing(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $startIndex, $endIndex)
    {
        for ($index = $endIndex - 1; $index > $startIndex; --$index) {
            if (!$this->isTestMethod($tokens, $index)) {
                continue;
            }
            $functionNameIndex = $tokens->getNextMeaningfulToken($index);
            $functionName = $tokens[$functionNameIndex]->getContent();
            $newFunctionName = $this->updateMethodCasing($functionName);
            if ($newFunctionName !== $functionName) {
                $tokens[$functionNameIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_STRING, $newFunctionName]);
            }
            $docBlockIndex = $this->getDocBlockIndex($tokens, $index);
            if ($this->hasDocBlock($tokens, $index)) {
                $this->updateDocBlock($tokens, $docBlockIndex);
            }
        }
    }
    /**
     * @param string $functionName
     *
     * @return string
     */
    private function updateMethodCasing($functionName)
    {
        if (self::CAMEL_CASE === $this->configuration['case']) {
            $newFunctionName = $functionName;
            $newFunctionName = \ucwords($newFunctionName, '_');
            $newFunctionName = \str_replace('_', '', $newFunctionName);
            $newFunctionName = \lcfirst($newFunctionName);
        } else {
            $newFunctionName = \MolliePrefix\PhpCsFixer\Utils::camelCaseToUnderscore($functionName);
        }
        return $newFunctionName;
    }
    /**
     * @param int $index
     *
     * @return bool
     */
    private function isTestMethod(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        // Check if we are dealing with a (non abstract, non lambda) function
        if (!$this->isMethod($tokens, $index)) {
            return \false;
        }
        // if the function name starts with test it's a test
        $functionNameIndex = $tokens->getNextMeaningfulToken($index);
        $functionName = $tokens[$functionNameIndex]->getContent();
        if ($this->startsWith('test', $functionName)) {
            return \true;
        }
        // If the function doesn't have test in its name, and no doc block, it's not a test
        if (!$this->hasDocBlock($tokens, $index)) {
            return \false;
        }
        $docBlockIndex = $this->getDocBlockIndex($tokens, $index);
        $doc = $tokens[$docBlockIndex]->getContent();
        if (\false === \strpos($doc, '@test')) {
            return \false;
        }
        return \true;
    }
    /**
     * @param int $index
     *
     * @return bool
     */
    private function isMethod(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $tokensAnalyzer = new \MolliePrefix\PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        return $tokens[$index]->isGivenKind(\T_FUNCTION) && !$tokensAnalyzer->isLambda($index);
    }
    /**
     * @param string $needle
     * @param string $haystack
     *
     * @return bool
     */
    private function startsWith($needle, $haystack)
    {
        return \substr($haystack, 0, \strlen($needle)) === $needle;
    }
    /**
     * @param int $index
     *
     * @return bool
     */
    private function hasDocBlock(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        $docBlockIndex = $this->getDocBlockIndex($tokens, $index);
        return $tokens[$docBlockIndex]->isGivenKind(\T_DOC_COMMENT);
    }
    /**
     * @param int $index
     *
     * @return int
     */
    private function getDocBlockIndex(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $index)
    {
        do {
            $index = $tokens->getPrevNonWhitespace($index);
        } while ($tokens[$index]->isGivenKind([\T_PUBLIC, \T_PROTECTED, \T_PRIVATE, \T_FINAL, \T_ABSTRACT, \T_COMMENT]));
        return $index;
    }
    /**
     * @param int $docBlockIndex
     */
    private function updateDocBlock(\MolliePrefix\PhpCsFixer\Tokenizer\Tokens $tokens, $docBlockIndex)
    {
        $doc = new \MolliePrefix\PhpCsFixer\DocBlock\DocBlock($tokens[$docBlockIndex]->getContent());
        $lines = $doc->getLines();
        $docBlockNeedsUpdate = \false;
        for ($inc = 0; $inc < \count($lines); ++$inc) {
            $lineContent = $lines[$inc]->getContent();
            if (\false === \strpos($lineContent, '@depends')) {
                continue;
            }
            $newLineContent = \MolliePrefix\PhpCsFixer\Preg::replaceCallback('/(@depends\\s+)(.+)(\\b)/', function (array $matches) {
                return \sprintf('%s%s%s', $matches[1], $this->updateMethodCasing($matches[2]), $matches[3]);
            }, $lineContent);
            if ($newLineContent !== $lineContent) {
                $lines[$inc] = new \MolliePrefix\PhpCsFixer\DocBlock\Line($newLineContent);
                $docBlockNeedsUpdate = \true;
            }
        }
        if ($docBlockNeedsUpdate) {
            $lines = \implode('', $lines);
            $tokens[$docBlockIndex] = new \MolliePrefix\PhpCsFixer\Tokenizer\Token([\T_DOC_COMMENT, $lines]);
        }
    }
}
