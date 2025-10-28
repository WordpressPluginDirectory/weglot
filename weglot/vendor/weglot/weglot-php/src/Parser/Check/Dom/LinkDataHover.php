<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

class LinkDataHover extends AbstractDomChecker
{
    const DOM = 'a';

    const PROPERTY = 'data-hover';

    const WORD_TYPE = WordType::TEXT;
}
