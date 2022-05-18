<?php

namespace robuust\formdesk\fields;

use Craft;
use craft\base\ElementInterface;
use craft\fields\Dropdown;

/**
 * Formdesk Field.
 *
 * @author    Bob Olde Hampsink <bob@robuust.digital>
 * @copyright Copyright (c) 2022, Robuust
 * @license   MIT
 *
 * @see       https://robuust.digital
 */
class Formdesk extends Dropdown
{
    /**
     * {@inheritdoc}
     */
    public static function displayName(): string
    {
        return Craft::t('app', 'Formdesk');
    }

    /**
     * {@inheritdoc}
     */
    public function getElementValidationRules(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function inputHtml($value, ElementInterface $element = null): string
    {
        /** @var SingleOptionFieldData $value */
        $options = $this->translatedOptions();

        return Craft::$app->getView()->renderTemplate('_includes/forms/select', [
            'id' => $this->getInputId(),
            'describedBy' => $this->describedBy,
            'name' => $this->handle,
            'value' => null,
            'options' => $options,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSettings(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsHtml()
    {
        return null;
    }
}
