<?php
namespace OyezTest\MediaTest\ArticleTest;

use Oyez\Common\Buffer;
use Oyez\Media\Article\Edition\LinearEdition;
use OyezTest\Support\Common;

class ArticleFake_FakeLinearEdition extends LinearEdition
{
    public $linear_spec = array(
        'title'     => null,
        'fieldA'    => '~AAA',
        'fieldB'    => '~BBB',
        'fieldC'    => '~CCC'
    );

    public function getLinearSpec()
    {
        return $this->linear_spec;
    }

    /** @var Buffer */
    protected $_buffer;

    protected function _init()
    {
        $this->_buffer = Common::getProtectedProperty($this->editor, '_buffer');
    }

    protected function _closed()
    {
        if ($this->isClean()) {   // use both for coverage
            $this->_buffer->write("CLEAN\n");
        }
        $this->_buffer->write("EOF\n");
    }

    protected function _formatHelper_field($field, $fn='_bufferFieldValue')
    {
        return parent::_formatHelper_field($field, $fn);
    }

    protected function _bufferFieldValue($field, $value)
    {
        $this->_buffer->write("{$field}:{$value}\n");
        return true;
    }
}

// END of file
