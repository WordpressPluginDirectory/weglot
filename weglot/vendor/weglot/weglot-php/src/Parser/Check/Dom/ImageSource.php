<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

class ImageSource extends AbstractDomChecker
{
    const DOM = 'img';

    const PROPERTY = 'src';

    const WORD_TYPE = WordType::IMG_SRC;
}
