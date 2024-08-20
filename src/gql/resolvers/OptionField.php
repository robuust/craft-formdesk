<?php

namespace robuust\formdesk\gql\resolvers;

use craft\gql\base\Resolver;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @author    Bob Olde Hampsink <bob@robuust.digital>
 * @copyright Copyright (c) 2022, Robuust
 * @license   MIT
 *
 * @see       https://robuust.digital
 */
class OptionField extends Resolver
{
    /**
     * {@inheritdoc}
     */
    public static function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        $fieldName = $resolveInfo->fieldName;
        $optionFieldData = $source->{$fieldName};

        $resolvedValue = '';

        if (is_array($optionFieldData) && count($optionFieldData)) {
            return $optionFieldData[0]['value'];
        }

        return $resolvedValue;
    }
}
