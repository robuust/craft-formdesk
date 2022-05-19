<?php

namespace robuust\formdesk;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use robuust\formdesk\fields\Formdesk as FormdeskField;
use robuust\formdesk\models\Settings;
use yii\base\Event;

/**
 * Formdesk plugin.
 */
class Plugin extends \craft\base\Plugin
{
    /**
     * Initializes the plugin.
     */
    public function init()
    {
        parent::init();

        $this->setComponents([
            'formdesk' => Craft::createGuzzleClient([
                'base_uri' => $this->settings->host,
                'headers' => [
                    'Authorization' => "Bearer {$this->settings->apiKey}",
                ],
            ]),
        ]);

        // Register fieldtype
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = FormdeskField::class;
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }
}
