<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

class LinkDataContent extends AbstractDomChecker
{
    const DOM = 'a';

    const PROPERTY = 'data-content';

    const WORD_TYPE = WordType::TEXT;
}
