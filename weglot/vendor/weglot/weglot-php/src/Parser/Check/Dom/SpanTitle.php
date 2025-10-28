<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

class SpanTitle extends AbstractDomChecker
{
    const DOM = 'span[title]';

    const PROPERTY = 'title';

    const WORD_TYPE = WordType::TEXT;
}
