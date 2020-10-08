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
namespace MolliePrefix\PhpCsFixer\Differ;

use MolliePrefix\PhpCsFixer\Diff\v3_0\Differ;
use MolliePrefix\PhpCsFixer\Diff\v3_0\Output\StrictUnifiedDiffOutputBuilder;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class FullDiffer implements \MolliePrefix\PhpCsFixer\Differ\DifferInterface
{
    /**
     * @var Differ
     */
    private $differ;
    public function __construct()
    {
        $this->differ = new \MolliePrefix\PhpCsFixer\Diff\v3_0\Differ(new \MolliePrefix\PhpCsFixer\Diff\v3_0\Output\StrictUnifiedDiffOutputBuilder(['collapseRanges' => \false, 'commonLineThreshold' => 100, 'contextLines' => 100, 'fromFile' => 'Original', 'toFile' => 'New']));
    }
    /**
     * {@inheritdoc}
     */
    public function diff($old, $new)
    {
        return $this->differ->diff($old, $new);
    }
}
