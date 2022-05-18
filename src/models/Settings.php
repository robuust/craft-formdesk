<?php

namespace robuust\formdesk\models;

use craft\base\Model;

/**
 * Settings model.
 */
class Settings extends Model
{
    /**
     * @var int
     */
    public $apiKey;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['apiKey'], 'required'],
        ];
    }
}
