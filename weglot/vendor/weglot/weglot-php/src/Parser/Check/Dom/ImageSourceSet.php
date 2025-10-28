<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

class ImageSourceSet extends AbstractDomChecker
{
    const DOM = 'img';

    const PROPERTY = 'srcset';

    const WORD_TYPE = WordType::IMG_SRC;
}
