<?php

namespace yiiunit\sphinx\data\ar;

/**
 * Test Sphinx ActiveRecord class
 */
class ActiveRecord extends \yii\sphinx\ActiveRecord
{
    public static $db;

    public static function getDb()
    {
        return self::$db;
    }
}
