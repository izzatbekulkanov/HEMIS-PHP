<?php

namespace common\components\hemis\models;

use common\components\hemis\HemisApiSyncModel;
use common\models\system\_BaseModel;
use common\models\system\SystemMessageTranslation;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 *
 * @property integer $id
 * @property string $model
 * @property string $model_id
 * @property string $description
 * @property string $status
 * @property string $error
 */
class SyncLog extends _BaseModel
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    protected $_searchableAttributes = ['model_id', 'description', 'error'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'e_system_sync_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['search'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);

        if ($this->search) {
            $query->orWhereLike('model', $this->search);
            $query->orWhereLike('model_id', $this->search);
            $query->orWhereLike('description', $this->search);
            $query->orWhereLike('error', $this->search);
        }

        return $dataProvider;
    }

    public static function registerModel(HemisApiSyncModel $model, $message = false, $delete = false)
    {
        return Yii::$app->db
            ->createCommand()
            ->insert(self::tableName(), [
                'model' => get_class($model),
                'model_id' => $model->getIdForSync(),
                'description' => $model->getDescriptionForSync(),
                'error' => mb_substr($message, 0, 2048),
                'delete' => $delete,
                'status' => $message ? self::STATUS_ERROR : self::STATUS_SUCCESS,
            ])
            ->execute();
    }
}
