<?php

namespace common\models\system;

use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\StringHelper;

/**
 *
 * @property int $id
 * @property int $_forwarded
 * @property int $_replied
 * @property int $_sender
 * @property int $_item
 * @property string[] $_recipients
 * @property string[] $sender_data
 * @property string[] $recipient_data
 * @property string[] $files
 * @property string $title
 * @property string $status
 * @property string $status_bd
 * @property string $message
 * @property DateTime $updated_at
 * @property DateTime $created_at
 * @property DateTime $send_on
 *
 * @property Contact $sender
 * @property AdminMessage $forwardedFromMessage
 * @property AdminMessage $repliedToMessage
 * @property AdminMessageItem $messageItem
 */
class AdminMessage extends _BaseModel
{
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_DELETED = 'delete';
    const STATUS_REMOVED = 'removed';

    const FOLDER_INBOX = 'inbox';
    const FOLDER_OUTBOX = 'outbox';
    const FOLDER_DRAFT = 'draft';
    const FOLDER_TRASH = 'trash';

    public static function tableName()
    {
        return 'e_admin_message';
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => __('Draft'),
            self::STATUS_SENT => __('Sent'),
            self::STATUS_DELETED => __('Deleted'),
        ];
    }

    public function getStatusLabel()
    {
        $labels = self::getStatusOptions();
        return isset($labels[$this->status]) ? $labels[$this->status] : $this->status;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['title', 'message', '_recipients'], 'required', 'when' => function () {
                return $this->status == self::STATUS_SENT;
            }],
            [['files', 'sender_data'], 'safe'],
            [['title'], 'string', 'max' => 256],
            [['message'], 'string', 'max' => 16000],
        ]);
    }

    public function beforeSave($insert)
    {
        $this->title = strip_tags($this->title);

        $this->message = strip_tags($this->message, '<p><a><b><pre><strong><i><h1><h2><h3><h4><h5><h6><blockquote><img><tr><td><th><thead><tbody><tfoot><br><ul><li><ol>');
        if (is_string($this->_recipients))
            $this->_recipients = array_filter(explode(',', $this->_recipients));

        return parent::beforeSave($insert);
    }


    public function getSender()
    {
        return $this->hasOne(Contact::class, ['id' => '_sender']);
    }

    public function getMessageItem()
    {
        return $this->hasOne(AdminMessageItem::class, ['id' => '_item']);
    }

    public function getForwardedFromMessage()
    {
        return $this->hasOne(self::class, ['id' => '_forwarded']);
    }

    public function getRepliedToMessage()
    {
        return $this->hasOne(self::class, ['id' => '_replied']);
    }

    public function search($params)
    {
        $this->load($params);

        $query = self::find()
            ->joinWith(['sender']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if ($this->search) {
            $query->orWhereLike('title', $this->search);
            $query->orWhereLike('e_admin_message_contact.name', $this->search);
            $query->orWhereLike('e_admin_message_contact.label', $this->search);
        }

        return $dataProvider;
    }

    public function getShortTitle($len = 6)
    {
        if ($this->title) {
            $pos = @mb_strpos($this->title, " ", 80);
            return ($pos !== false ? StringHelper::truncate($this->title, $pos) . ' ...' : $this->title);
        }

        return __('Draft message on {date}', ['date' => \Yii::$app->formatter->asDatetime($this->created_at->getTimestamp())]);
    }

    public function sendMessage()
    {
        $this->status = AdminMessage::STATUS_SENT;
        $this->sender_data = ['id' => $this->_sender, 'name' => $this->sender->name];
        $this->send_on = $this->getTimestampValue();

        if ($this->validate()) {
            $batch = [];
            $date = $this->send_on->format('Y-m-d H:i:s');
            $recipientsData = [];

            if (is_string($this->_recipients))
                $this->_recipients = explode(',', $this->_recipients);

            /**
             * @var $recipient Admin
             */
            $ids = [];

            foreach ($this->_recipients as $item) {
                if (is_numeric($item)) {
                    $ids[] = $item;
                }
            }

            $recipients = Contact::find()
                ->where(['id' => $ids])
                ->all();

            foreach ($recipients as $recipient) {
                $recipientsData[] = [
                    'id' => $recipient->id,
                    'name' => $recipient->name,
                ];

                $batch[] = [
                    '_message' => $this->id,
                    '_sender' => $this->_sender,
                    '_recipient' => $recipient->id,
                    'type' => AdminMessageItem::TYPE_INBOX,
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
            }

            if (count($batch)) {
                $this->recipient_data = $recipientsData;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($this->save()) {
                        if ($count = Yii::$app->db
                            ->createCommand()
                            ->batchInsert(AdminMessageItem::tableName(), array_keys($batch[0]), $batch)
                            ->execute()) {
                            $this->messageItem->updateAttributes(['type' => AdminMessageItem::TYPE_OUTBOX]);

                            $transaction->commit();
                            return $count;
                        }
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                }
            }
        }

        return false;
    }


    public function saveAsDraft()
    {
        $recipientsData = [];

        /**
         * @var $recipient Admin
         */
        if (is_string($this->_recipients))
            $this->_recipients = array_filter(explode(',', $this->_recipients));

        /**
         * @var $recipient Contact
         */
        $ids = [];

        foreach ($this->_recipients as $item) {
            if (is_numeric($item)) {
                $ids[] = $item;
            }
        }

        $recipients = Contact::find()
            ->where(['id' => $ids])
            ->all();

        foreach ($recipients as $recipient) {
            $recipientsData[] = [
                'id' => $recipient->id,
                'name' => $recipient->name,
            ];
        }

        $this->recipient_data = $recipientsData;

        return $this->save(false);
    }

    public function isNotSent()
    {
        return $this->status != self::STATUS_SENT;
    }

    public function isDeleted()
    {
        return $this->status == self::STATUS_DELETED;
    }

    public function getRecipientInformation($count = 4)
    {
        $array = $this->recipient_data;

        if (is_array($array) && !empty($array)) {
            $array = array_column($array, 'name');
            return count($array) > $count ? implode(', ', array_slice($array, 0, $count)) . ' ...' : implode(', ', $array);
        }

        return $this->getSenderInformation();
    }

    public function getSenderInformation($key = false)
    {
        $data = $this->sender_data;
        if ($key && isset($data[$key])) {
            return Html::encode($data[$key]);
        }
        if ($this->sender) {
            return $this->sender->name;
        }

        return '-';
    }


    /**
     * @param $admin
     * @param AdminMessage|null $forwardMessage
     * @param AdminMessage|null $replyMessage
     * @param string $title
     * @return AdminMessage|false
     */
    public static function createDraftMessage($admin, AdminMessage $forwardMessage = null, AdminMessage $replyMessage = null, $title = '')
    {
        $model = new AdminMessage();
        $model->status = AdminMessage::STATUS_DRAFT;
        $model->title = $title;
        if ($admin->contact)
            $model->_sender = $admin->contact->id;

        if ($replyMessage) {
            $model->title = __('RE: {message}', ['message' => $replyMessage->title]);
            $model->_replied = $replyMessage->id;
            $model->_recipients = [$replyMessage->_sender];
        }
        if ($forwardMessage) {
            $model->title = __('FWD: {message}', ['message' => $forwardMessage->title]);
            $model->message = Html::tag('p', __('Forwarded message:')) . Html::tag('blockquote', $forwardMessage->message);
            $model->_forwarded = $forwardMessage->id;
            $model->files = $forwardMessage->files;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->save(false)) {
                $item = new AdminMessageItem();
                $item->type = AdminMessageItem::TYPE_DRAFT;
                $item->_message = $model->id;
                $item->_sender = $admin->contact->id;
                if ($item->save(false)) {
                    $model->updateAttributes(['_item' => $item->id]);
                    $transaction->commit();
                    return $model;
                }
            }
        } catch (\Exception $e) {

        }
        $transaction->rollBack();

        return false;
    }
}
