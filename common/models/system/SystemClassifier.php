<?php

namespace common\models\system;

use common\components\Config;
use common\components\db\PgQuery;
use common\components\hemis\HemisApi;
use common\components\hemis\HemisApiSyncModel;
use common\models\system\classifier\_BaseClassifier;
use DateInterval;
use DateTime;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\console\controllers\MigrateController;
use yii\data\ActiveDataProvider;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\db\Migration;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * @property integer $id
 * @property string $classifier
 * @property string $name
 * @property boolean $status
 * @property integer $version
 * @property integer $_qid
 * @property integer $_sync
 * @property integer $position
 */
class SystemClassifier extends HemisApiSyncModel
{
    protected $_translatedAttributes = ['name'];

    public $import;

    public static function tableName()
    {
        return 'e_system_classifier';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['version', 'position'], 'number'],
            [['name', 'classifier'], 'required'],
            [['classifier'], 'unique'],
            [['import'], 'required', 'on' => 'import'],
            [['classifier'], 'match', 'pattern' => '/^[a-z0-9_]{3,255}$/', 'message' => __('Use only alpha-number characters and underscore')],
        ]);
    }


    public function search($params)
    {
        $this->load($params);

        $query = self::find();

        //$query->orderByTranslationField('name');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'name',
                    'position',
                    'version',
                    'classifier',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        if ($this->search) {
            foreach (Config::getShortLanguageCodes() as $code)
                $query->orWhereLike("name_$code", $this->search, '_translations');

            $query->orWhereLike('classifier', $this->search);
        }

        return $dataProvider;
    }

    private static function loadClassifiersFromFile($classifiersFile = false)
    {
        return @json_decode(file_get_contents($classifiersFile ? $classifiersFile : Yii::getAlias('@common/data/classifiers.json')), true);
    }

    public static function deleteClassifiersTables(Migration $migration)
    {
        if ($classData = self::loadClassifiersFromFile()) {
            foreach ($classData as $classifier => $description) {
                if ($migration->db->schema->getTableSchema($classifier, true) !== null) {
                    $migration->dropTable($classifier);
                }
            }
        }
    }

    public static function createClassifiersTables(Migration $migration, $version = -1, $classifiersFile = false)
    {

        $tableOptions = null;

        if ($migration->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $position = 0;
        if ($classData = self::loadClassifiersFromFile($classifiersFile)) {
            $transaction = Yii::$app->db->beginTransaction();

            try {

                if ($migration->db->schema->getTableSchema('e_system_classifier', true) === null) {
                    $migration->createTable('e_system_classifier', [
                        'id' => $migration->primaryKey(),
                        'classifier' => $migration->string(64)->notNull()->unique(),
                        'name' => $migration->text(),
                        'version' => $migration->integer()->defaultValue(0),
                        'options' => 'jsonb',
                        '_translations' => 'jsonb',
                        'position' => $migration->integer(3)->defaultValue(0),
                        'updated_at' => $migration->dateTime()->notNull(),
                        'created_at' => $migration->dateTime()->notNull(),
                    ], $tableOptions);
                }

                foreach ($classData as $classifier => $data) {
                    $description = $data[Config::LANGUAGE_UZBEK];

                    if ($migration->db->schema->getTableSchema($classifier, true) === null) {
                        $migration->createTable($classifier, [
                            'code' => $migration->string(64)->notNull()->unique(),
                            'name' => $migration->string(256)->notNull(),
                            'position' => $migration->integer(3)->defaultValue(0),
                            'active' => $migration->boolean()->defaultValue(true),
                            '_parent' => $migration->string(64)->null(),
                            '_translations' => 'jsonb',
                            '_options' => 'jsonb',
                            'updated_at' => $migration->dateTime()->notNull(),
                            'created_at' => $migration->dateTime()->notNull(),
                        ], $tableOptions);

                        $migration->addPrimaryKey("pk_$classifier", $classifier, 'code');
                        $migration->createIndex("idx_{$classifier}_position", $classifier, 'position');
                        $migration->createIndex("idx_{$classifier}_active", $classifier, 'active');
                        $migration->addCommentOnTable($classifier, $description);

                        $migration->addForeignKey(
                            "fk_{$classifier}_parent_code",
                            $classifier,
                            '_parent',
                            $classifier,
                            'code',
                            'RESTRICT',
                            'CASCADE'
                        );
                    }

                    //create entry for classifiers list
                    $model = SystemClassifier::findOne(['classifier' => $classifier]);

                    if ($model == null) {
                        $model = new SystemClassifier([
                            'classifier' => $classifier,
                            'name' => $description,
                            'position' => $position,
                        ]);
                        $model->save();
                    }

                    foreach (Config::getShortLanguageCodes() as $lang => $code) {
                        if (isset($data[$lang]) && $data[$lang]) {
                            $model->setTranslation('name', $data[$lang], $lang);
                        } else {
                            $model->setTranslation('name', __($description, [], $lang), $lang);
                        }
                    }

                    $model->updateAttributes(['_translations' => $model->_translations, 'version' => $version]);


                    //create classifiers model
                    $name = Inflector::camelize(substr($classifier, 1));
                    $file = Yii::getAlias("@root/common/models/system/classifier/{$name}.php");
                    $class = '\common\models\system\classifier\\' . $name;

                    if (!file_exists($file)) {
                        $template = Yii::getAlias("@root/common/models/system/classifier/_template.tpl");
                        file_put_contents($file, str_replace('{classifier}', $classifier, str_replace('{name}', $name, file_get_contents($template))));
                    }

                    if (!HEMIS_INTEGRATION || count($data['options']) < 500) {
                        $innerTrans = Yii::$app->db->beginTransaction();
                        $success = 0;
                        /**
                         * @var $class _BaseClassifier
                         */
                        try {
                            $p = 0;
                            foreach ($data['options'] as $option) {
                                $option['version'] = $version;
                                if ($class::importDataCols($option, $p)) {
                                    $success++;
                                }
                            }
                            $innerTrans->commit();
                        } catch (Exception $e) {
                            $success = 0;
                            $innerTrans->rollBack();
                        }
                    }

                    $position++;
                }

                $transaction->commit();

                return $position;
            } catch (\Exception $e) {
                echo $e->getMessage();
                $transaction->rollBack();
            }
        }


        return false;
    }

    /**
     * @return _BaseClassifier
     */
    public function getClassifierClassName()
    {
        $name = Inflector::camelize(substr($this->classifier, 1));
        return '\common\models\system\classifier\\' . $name;
    }

    public static function importData($data, $class)
    {
        $language = Yii::$app->language;
        Yii::$app->language = Config::LANGUAGE_UZBEK;
        $items = explode(PHP_EOL, $data);
        $success = 0;
        $transaction = Yii::$app->db->beginTransaction();

        try {
            foreach ($items as $i => $item) {
                $cols = array_filter(explode("\t", $item));
                if (count($cols)) {
                    if ($class::importData($cols, $i)) {
                        $success++;
                    }
                }
            }
            $transaction->commit();
        } catch (Exception $e) {
            $success = 0;
            $transaction->rollBack();
        }
        Yii::$app->language = $language;

        return $success;
    }

    public function getDescriptionForSync()
    {
        return $this->name;
    }

    public function getIdForSync()
    {
        return $this->classifier;
    }

    public static function getModel($id)
    {
        return self::findOne(['classifier' => $id]);
    }


    /**
     * @return _BaseClassifier[]
     */
    public function getClassifierOptions()
    {
        $class = $this->getClassifierClassName();
        $items = $class::find()
            ->indexBy('code')
            ->all();

        return $items;
    }

    public function updateClassifierData($data)
    {
        /**
         *
         * {
         * "_entityName": "hemishe_HCitizenship",
         * "id": "11",
         * "code": "11",
         * "name": "O‘zbekiston Respublikasi fuqarosi",
         * "version": 1
         * }
         *  [381] => Array
         * (
         * [_entityName] => hemishe_HSpecialityBachelor
         * [id] => 3aef7205-1f1d-43d1-b4d6-e73eb77be65c
         * [parent] => Array
         * (
         * [_entityName] => hemishe_HSpecialityBachelor
         * [id] => 8ce773c0-704f-4313-9dd3-a21874fa5fad
         * [code] => 110000
         * [name] => Pedagogika
         * [active] => 1
         * [version] => 1
         * )
         * [code] => 5110100
         * [name] => Matematika o‘qitish metodikasi
         * [active] => 1
         * [version] => 2
         * )
         */
        $count = 0;

        if ($this->version != $data['version'] || true) {
            $className = $this->getClassifierClassName();
            $this->setTranslation('name', $data['title'], Config::LANGUAGE_UZBEK);
            $count = $this->updateAttributes([
                '_translations' => $this->_translations,
                'version' => $data['version'],
                'updated_at' => $this->getTimestampValue(),
            ]);

            $transaction = Yii::$app->db->beginTransaction();
            $position = 0;
            $count = 0;
            try {
                $uniqueField = $className::getUniqueFieldName();

                $options = $className::find()
                    ->indexBy($uniqueField)
                    ->all();

                $className::importOptionsFromApi($className, $data['items'], $position, $count, $options);
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                SystemLog::captureAction($e->getMessage());
                throw new \Exception($e->getMessage());
            }
        }

        return $count;
    }
}
