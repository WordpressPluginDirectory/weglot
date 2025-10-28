<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

class LinkDataText extends AbstractDomChecker
{
    const DOM = 'a';

    const PROPERTY = 'data-text';

    const WORD_TYPE = WordType::TEXT;
}
