<?php

/**
 * @author Lajos Molnár <lajax.m@gmail.com>
 * @since 1.0
 */

namespace lajax\translatemanager\models;

use Yii;
use yii\mongodb\ActiveRecord;

/**
 * This is the model class for table "language_source".
 *
 * @property string $id
 * @property string $category
 * @property string $message
 * @property string $source
 * @property string $translation
 *
 * @property LanguageTranslate $languageTranslate0
 * @property LanguageTranslate $languageTranslate
 * @property Language[] $languages
 */
class LanguageSource extends ActiveRecord
{

    const INSERT_LANGUAGE_ITEMS_LIMIT = 10;

    /**
     * @return array
     */
    public static function collectionName()
    {
        return ['product-suite', 'language_source'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'id',
            'category',
            'message',
            'source',
            'translation',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message'], 'string'],
            [['category'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('model', 'ID'),
            'category' => Yii::t('model', 'Category'),
            'message' => Yii::t('model', 'Message'),
        ];
    }

    /**
     * Inserting new language elements into the language_source table.
     * @param array $languageItems
     * @return integer The number of new language elements.
     */
    public function insertLanguageItems($languageItems)
    {
        $data = [];
        foreach ($languageItems as $category => $messages) {
            foreach (array_keys($messages) as $message) {
                $data[] = [
                    'category' => $category,
                    'message' => $message
                ];
            }
        }

        $count = count($data);
        $collection = Yii::$app->mongodb->getCollection('language_source');

        for ($i = 0; $i < $count; $i += self::INSERT_LANGUAGE_ITEMS_LIMIT) {
            $collection->batchInsert(array_slice($data, $i, self::INSERT_LANGUAGE_ITEMS_LIMIT), []);
//            $language_item = new LanguageSource();
//            $language_item->category = $data[$i];
//            static::getDb()
//                    ->createCommand()
//                    ->batchInsert(static::tableName(), ['category', 'message'], array_slice($data, $i, self::INSERT_LANGUAGE_ITEMS_LIMIT))
//                    ->execute();
        }

        return $count;
    }

    /**
     * @return string
     */
    public function getTranslation()
    {
        return $this->languageTranslate ? $this->languageTranslate->translation : '';
    }

    /**
     * @return string
     */
    public function getSource()
    {
        if ($this->languageTranslate0 && $this->languageTranslate0->translation) {
            return $this->languageTranslate0->translation;
        } else {
            return $this->message;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     * @deprecated since version 1.5.3
     */
    public function getLanguageTranslateByLanguage()
    {
        return $this->getLanguageTranslate();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguageTranslate0()
    {
        return $this->getLanguageTranslate();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguageTranslate()
    {
        return $this->hasOne(LanguageTranslate::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguageTranslates()
    {
        return $this->hasMany(LanguageTranslate::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguages()
    {
        return $this->hasMany(Language::className(), ['language_id' => 'language'])
                        ->viaTable(LanguageTranslate::tableName(), ['id' => 'id']);
    }

}
