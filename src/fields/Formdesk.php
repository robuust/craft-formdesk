<?php

namespace robuust\formdesk\fields;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\fields\Dropdown;
use craft\helpers\Json;
use GraphQL\Type\Definition\Type;
use robuust\formdesk\gql\resolvers\OptionField as OptionFieldResolver;
use robuust\formdesk\Plugin;

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
     * @var Plugin;
     */
    public $plugin;

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
    public function init(): void
    {
        parent::init();

        $this->plugin = Plugin::getInstance();

        // Get all lists
        $results = Craft::$app->getCache()->get('formdesk');
        if ($results === false) {
            try {
                $request = $this->plugin->formdesk->get('forms');
                $results = Json::decode((string) $request->getBody());

                Craft::$app->getCache()->set('formdesk', $results);
            } catch (\Exception) {
                $results = [];
            }
        }

        // Set as dropdown options
        foreach ($results as $result) {
            $this->options[] = [
                'value' => $result['id'],
                'label' => $result['name'],
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(ElementInterface $element): ?array
    {
        // If the value is invalid and has a default value (which is going to be pulled in via inputHtml()),
        // preemptively mark the field as modified
        /** @var SingleOptionFieldData $value */
        $value = $element->getFieldValue($this->handle);

        if (!isset($value[0]) || !$value[0]['valid'] && $this->defaultValue() !== null) {
            return [
                Element::ATTR_STATUS_MODIFIED,
                Craft::t('app', 'This field has been modified.'),
            ];
        }

        return Field::getStatus($element);
    }

    /**
     * {@inheritdoc}
     */
    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        // Get list id
        $list = parent::normalizeValue($value, $element);

        try {
            $request = $this->plugin->formdesk->get("forms/{$list}/items");
            $results = Json::decode((string) $request->getBody());

            // Add hidden list id field
            $fields = [
                [
                    'id' => (string) $list,
                    'name' => 'list_id',
                    'label' => Craft::t('site', 'List'),
                    'type' => 'hidden',
                    'value' => $list,
                    'valid' => true,
                    'options' => [],
                ],
            ];

            // Add list fields
            foreach ($results as $result) {
                $fields[] = [
                    'id' => $result['id'],
                    'name' => $result['identifier'],
                    'label' => $result['label'],
                    'type' => $this->getType($result),
                    'value' => $result['defaultvalue'],
                    'valid' => true,
                    'options' => $result['options'] ?? [],
                ];
            }
        } catch (\Exception) {
            $fields = [];
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        if (is_array($value) && count($value)) {
            $value = $value[0]['value'];
        }

        return parent::serializeValue($value, $element);
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
    protected function inputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        /** @var SingleOptionFieldData $value */
        $options = $this->translatedOptions();

        return Craft::$app->getView()->renderTemplate('_includes/forms/select', [
            'id' => $this->getInputId(),
            'describedBy' => $this->describedBy,
            'name' => $this->handle,
            'value' => $value[0]['value'] ?? null,
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
    public function getSettingsHtml(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentGqlType(): Type|array
    {
        $type = parent::getContentGqlType();

        $type['resolve'] = OptionFieldResolver::class.'::resolve';

        return $type;
    }

    /**
     * Get type.
     *
     * @param array $field
     *
     * @return string
     */
    private function getType(array $field): string
    {
        if ($field['datatype'] == 'date') {
            return $field['datatype'];
        }

        if ($field['itemtype'] == 'radiogroup') {
            return 'radio';
        }

        return $field['itemtype'];
    }
}
