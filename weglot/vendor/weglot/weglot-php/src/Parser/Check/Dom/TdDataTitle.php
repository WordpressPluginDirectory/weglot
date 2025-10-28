<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

class TdDataTitle extends AbstractDomChecker
{
    const DOM = 'td';

    const PROPERTY = 'data-title';

    const WORD_TYPE = WordType::VALUE;
}
