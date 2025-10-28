<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

class LinkDataValue extends AbstractDomChecker
{
    const DOM = 'a';

    const PROPERTY = 'data-value';

    const WORD_TYPE = WordType::TEXT;
}
