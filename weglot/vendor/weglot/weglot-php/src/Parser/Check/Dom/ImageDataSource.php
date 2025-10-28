<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

class ImageDataSource extends AbstractDomChecker
{
    const DOM = 'img';

    const PROPERTY = 'data-src';

    const WORD_TYPE = WordType::IMG_SRC;
}
