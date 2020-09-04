<?php

declare(strict_types=1);

namespace As247\WpEloquent\Doctrine\Inflector\Rules\English;

use As247\WpEloquent\Doctrine\Inflector\GenericLanguageInflectorFactory;
use As247\WpEloquent\Doctrine\Inflector\Rules\Ruleset;

final class InflectorFactory extends GenericLanguageInflectorFactory
{
    protected function getSingularRuleset() : Ruleset
    {
        return Rules::getSingularRuleset();
    }

    protected function getPluralRuleset() : Ruleset
    {
        return Rules::getPluralRuleset();
    }
}
