<?php

namespace Neutrino;

class Version extends \Phalcon\Version
{
    /**
     * @inheritdoc
     */
    protected static function _getVersion()
    {
        return [
            1, // major
            1, // medium
            2, // minor
            4, // special
            0  // number
        ];
    }
}
