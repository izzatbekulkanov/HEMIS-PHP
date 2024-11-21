<?php


namespace common\components;


use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\i18n\DbMessageSource as DbMs;

class DbMessageSource extends DbMs
{
    protected function loadMessagesFromDb($category, $language)
    {
        if (\Yii::$app->db->schema->getTableSchema($this->sourceMessageTable, true) !== null) {
            $mainQuery = (new Query())->select(['message' => 't1.message', 'translation' => 't2.translation'])
                ->from(['t1' => $this->sourceMessageTable, 't2' => $this->messageTable])
                ->where([
                    't1.id' => new Expression('[[t2.id]]'),
                    't1.category' => $category,
                    't2.language' => $language,
                ]);

            $fallbackLanguage = substr($language, 0, 2);
            $fallbackSourceLanguage = substr($this->sourceLanguage, 0, 2);

            if ($fallbackLanguage !== $language) {
                $mainQuery->union($this->createFallbackQuery($category, $language, $fallbackLanguage), true);
            } elseif ($language === $fallbackSourceLanguage) {
                $mainQuery->union($this->createFallbackQuery($category, $language, $fallbackSourceLanguage), true);
            }

            $messages = $mainQuery->createCommand($this->db)->queryAll();

            return ArrayHelper::map($messages, 'message', 'translation');
        }

        return [];
    }
}