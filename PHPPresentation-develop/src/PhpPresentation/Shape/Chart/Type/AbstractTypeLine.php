<?php
/**
 * This file is part of PHPPresentation - A pure PHP library for reading and writing
 * presentations documents.
 *
 * PHPPresentation is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPPresentation/contributors.
 *
 * @see        https://github.com/PHPOffice/PHPPresentation
 *
 * @copyright   2009-2015 PHPPresentation contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpPresentation\Shape\Chart\Type;

class AbstractTypeLine extends AbstractType
{
    /**
     * Is Line Smooth?
     *
     * @var bool
     */
    protected $isSmooth = false;

    /**
     * Is Line Smooth?
     *
     * @return bool
     */
    public function isSmooth(): bool
    {
        return $this->isSmooth;
    }

    /**
     * Set Line Smoothness
     *
     * @param bool $value
     *
     * @return AbstractTypeLine
     */
    public function setIsSmooth(bool $value = true): AbstractTypeLine
    {
        $this->isSmooth = $value;

        return $this;
    }

    /**
     * Get hash code.
     *
     * @return string Hash code
     */
    public function getHashCode(): string
    {
        return md5($this->isSmooth() ? '1' : '0');
    }
}
