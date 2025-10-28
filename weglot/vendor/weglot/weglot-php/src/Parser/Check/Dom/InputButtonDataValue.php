<?php

namespace Weglot\Parser\Check\Dom;

use Weglot\Client\Api\Enum\WordType;

class InputButtonDataValue extends AbstractDomChecker
{
    const DOM = 'input[type="submit"],input[type="button"]';

    const PROPERTY = 'data-value';

    const WORD_TYPE = WordType::TEXT;
}
