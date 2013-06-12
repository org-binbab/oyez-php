<?php
/**
 * "Oyez" - The Universal Format Library
 *
 * @author      BinaryBabel OSS (<projects@binarybabel.org>)
 * @link        http://code.binbab.org
 * @copyright   Copyright 2013 sha1(OWNER) = df334a7237f10846a0ca302bd323e35ee1463931
 * @license     GNU Lesser General Public License v3 (see LICENSE and COPYING files)
 */

namespace Oyez\Media\Article\Edition;

use Oyez\Media\Article\Edition;
use Oyez\Media\Exception;

abstract class LinearEdition extends Edition
{
    const NO_DEFAULT = null;

    /** @codeCoverageIgnore */
    abstract public function getLinearSpec();

    public function field($field)
    {
        parent::field($field);
        if ('closed' == $field) {
            $field = null;
        }
        $this->_formatHelper_linear($this->getLinearSpec(), $field);
    }

    public function closed()
    {
        parent::closed();
        if ( ! $this->_formatHelper_linear($this->getLinearSpec())) {
            throw new Exception("Attempted to close incomplete article.");
        }
    }

    protected function _field($field)
    {
    }

    /**
     * Use a spec array to help generate a linear format.
     * The $format_spec takes the form [FIELD_NAME] => DEFAULT_VALUE
     *
     * _formatHelper_field() will be used for each field's format.
     * Fields are automatically locked if the format function returns true.
     *
     * If $field is given, all fields are evaluated up (and including) it.
     *  - If a field is locked, it is skipped over.
     *  - If a field is not locked, but has a value, it is formatted.
     *  - If a field does not have a value, the function is aborted.
     *
     * Returns true if all fields have been formatted.
     *
     * @param array $linear_spec
     * @param null $field
     * @return bool
     * @throws Exception
     */
    protected function _formatHelper_linear(array $linear_spec, $field=null)
    {
        if (empty($linear_spec)) {
            throw new Exception("Linear spec cannot be empty.");
        }

        $spec_fields = array_keys($linear_spec);
        if (empty($field)) {
            $field = end($spec_fields);
        }

        $field_index = array_search($field, $spec_fields);
        if ($field_index === false) {
            return false;
        }

        for ($i=0; $i<=$field_index; $i++) {
            $xField = $spec_fields[$i];

            if ($this->_article->isFieldLocked($xField)) {
                continue;
            }

            if (null === $this->_article->$xField) {
                $xField_default = $linear_spec[$xField];
                if (null === $xField_default) {
                    break;
                }
                $this->_article->$xField = $xField_default;
            }

            // Second lock check required in case defaults updated.
            if ($this->_article->isFieldLocked($xField)) {
                continue;
            }

            if ($this->_formatHelper_field($xField)) {
                $this->_article->lockField($xField);
            }
        }

        return ($i >= count($spec_fields));
    }
}

// END of file
