<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

class LinkTitle extends AbstractDomChecker
{
    const DOM = 'a';

    const PROPERTY = 'title';

    const WORD_TYPE = WordType::TEXT;

    const ESCAPE_SPECIAL_CHAR = true;
}
