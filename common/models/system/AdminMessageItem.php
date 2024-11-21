<?php

namespace common\models\system;

use common\models\structure\EDepartment;
use common\models\system\classifier\StructureType;
use DateTime;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 *
 * @property int $id
 * @property int $_message
 * @property int $_recipient
 * @property int $_sender
 * @property int $_folder
 * @property int $_label
 * @property boolean opened
 * @property boolean deleted
 * @property boolean starred
 * @property string type
 * @property DateTime $updated_at
 * @property DateTime $created_at
 * @property DateTime $deleted_at
 * @property DateTime $opened_at
 *
 * @property AdminMessage $message
 * @property Admin $recipient
 * @property Contact $sender
 */
class AdminMessageItem extends _BaseModel
{
    const TYPE_INBOX = 'inbox';
    const TYPE_OUTBOX = 'outbox';
    const TYPE_DRAFT = 'draft';
    const TYPE_TRASH = 'trash';

    public static function tableName()
    {
        return 'e_admin_message_item';
    }


    public function getRecipient()
    {
        return $this->hasOne(Contact::class, ['id' => '_recipient']);
    }

    public function getSender()
    {
        return $this->hasOne(Contact::class, ['id' => '_sender']);
    }

    public function getMessage()
    {
        return $this->hasOne(AdminMessage::class, ['id' => '_message']);
    }

    public static function getFolderTitle($folder)
    {
        $options = self::getFolderOptions();

        return isset($options[$folder]) ? $options[$folder] : $folder;
    }

    public static function getFolderOptions()
    {
        return [
            self::TYPE_INBOX => __('Inbox'),
            self::TYPE_OUTBOX => __('Sent'),
            self::TYPE_DRAFT => __('Draft'),
            self::TYPE_TRASH => __('Trash'),
        ];
    }

    public static function getUnReadInboxCount($admin)
    {
        if($admin->contact) {
            return self::find()
                ->where(['type' => self::TYPE_INBOX, 'opened' => false, 'deleted' => false, '_recipient' => $admin->contact->id])
                ->count();
        }
    }

    /**
     * @param $admin
     * @param int $limit
     * @return self[]
     */
    public static function getInboxMessages($admin, $limit = 6, $sort = ['opened' => SORT_ASC, 'created_at' => SORT_DESC])
    {
        if($admin->contact) {
            return self::find()
                ->with(['message'])
                ->where(['type' => self::TYPE_INBOX, 'deleted' => false, '_recipient' => $admin->contact->id])
                ->limit($limit)
                ->orderBy($sort)
                ->all();
        }
    }


    public static function cleanTrashMessages($admin)
    {
        return self::deleteAll([
            'AND',
            ['deleted' => true],
            [
                'OR',
                ['AND', ['_sender' => $admin->contact->id], ['type' => [self::TYPE_OUTBOX, self::TYPE_DRAFT]]],
                ['AND', ['_recipient' => $admin->contact->id], ['type' => self::TYPE_INBOX]]
            ]
        ]);
    }

    public static function getFolderCounters($admin)
    {
        if($admin->contact) {
            return [
                self::TYPE_INBOX => self::find()
                    ->where(['type' => self::TYPE_INBOX, 'opened' => false, 'deleted' => false, '_recipient' => $admin->contact->id])
                    ->count(),

                self::TYPE_OUTBOX => self::find()
                    ->where(['type' => self::TYPE_OUTBOX, 'deleted' => false, '_sender' => $admin->contact->id])
                    ->count(),

                self::TYPE_DRAFT => self::find()
                    ->where(['type' => self::TYPE_DRAFT, 'deleted' => false, '_sender' => $admin->contact->id])
                    ->count(),

                self::TYPE_TRASH => self::find()
                    ->where([
                        'AND',
                        ['deleted' => true],
                        [
                            'OR',
                            ['AND', ['_sender' => $admin->contact->id], ['type' => [self::TYPE_OUTBOX, self::TYPE_DRAFT]]],
                            ['AND', ['_recipient' => $admin->contact->id], ['type' => self::TYPE_INBOX]]
                        ]
                    ])
                    ->count(),
            ];
        }
    }

    public function searchMessages($params, $folder, $admin)
    {

        $this->load($params);
        $size = 50;
        $query = AdminMessageItem::find()
            ->joinWith(['message', 'sender']);

        if ($this->search) {
            $query->orWhereLike('e_admin_message.message', $this->search);
            $query->orWhereLike('e_admin_message.title', $this->search);
            $query->orWhereLike('e_admin_message_contact.name', $this->search);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => $size,
            ],
        ]);

        if ($folder == self::TYPE_INBOX) {
            if($admin->contact) {
                $query
                    ->andFilterWhere(['_recipient' => $admin->contact->id, 'e_admin_message_item.type' => self::TYPE_INBOX, 'deleted' => false]);
            }
            return $dataProvider;
        }

        if ($folder == self::TYPE_OUTBOX) {
            if($admin->contact) {
                $query->andFilterWhere(['e_admin_message_item._sender' => $admin->contact->id, 'e_admin_message_item.type' => self::TYPE_OUTBOX, 'deleted' => false]);
            }
            return $dataProvider;
        }

        if ($folder == self::TYPE_DRAFT) {
            if($admin->contact) {
                $query->andFilterWhere(['e_admin_message_item._sender' => $admin->contact->id, 'e_admin_message_item.type' => self::TYPE_DRAFT, 'deleted' => false]);
            }
            return $dataProvider;
        }

        if ($folder == self::TYPE_TRASH) {
            if($admin->contact) {
                $query->where([
                    'AND',
                    [
                        'AND',
                        ['deleted' => true],
                        [
                            'OR',
                            ['AND', ['e_admin_message_item._sender' => $admin->contact->id], ['e_admin_message_item.type' => [self::TYPE_OUTBOX, self::TYPE_DRAFT]]],
                            ['AND', ['_recipient' => $admin->contact->id], ['e_admin_message_item.type' => self::TYPE_INBOX]]
                        ]
                    ],
                    !$this->search ? ['deleted' => true] : [
                        'OR',
                        new Expression("lower(e_admin_message.title) like lower(:search) OR lower(e_admin_message.message) like lower(:search)"),
                        new Expression("lower(e_admin.full_name) like lower(:search)")
                    ]
                ],
                    !$this->search ? [] : ['search' => '%' . $this->search . '%']
                );
            }
            return $dataProvider;
        }
    }


    public function getTimeFormatted()
    {
        $date = $this->created_at;

        if ($this->created_at instanceof DateTime) {
            $date = $this->created_at;
        }
        $diff = time() - $date->getTimestamp();

        if ($diff < 300) {
            return __('Hozirgina');
        } elseif ($diff < 3600) {
            return __('{minute} minut avval', ['minute' => round($diff / 60)]);
        } elseif ($diff < 3600 * 3) {
            return __('{hour} soat avval', ['hour' => round($diff / 3600)]);
        } elseif ($diff < 86400) {
            $today = new DateTime();
            $today->setTime(0, 0, 0);

            $match_date = new DateTime();
            $match_date->setTimestamp($date->getTimestamp());
            $match_date->setTime(0, 0, 0);

            $diff = $today->diff($match_date);
            $diffDays = (integer)$diff->format("%R%a");
            switch ($diffDays) {
                case 0:
                    //today
                    return __('Bugun, {time}', ['time' => Yii::$app->formatter->asDate($date->getTimestamp(), 'php:H:i')]);
                    break;
                case -1:
                    //Yesterday
                    return __('Kecha, {time}', ['time' => Yii::$app->formatter->asDate($date->getTimestamp(), 'php:H:i')]);
                    break;
            }

            return Yii::$app->formatter->asDate($date->getTimestamp(), 'php:d/M, H:i');
        } elseif ($diff < 31536000) {
            return Yii::$app->formatter->asDate($date->getTimestamp(), 'php:d/m, H:i');
        }
        return Yii::$app->formatter->asDate($date->getTimestamp());
    }


    public function isDeleted()
    {
        return $this->deleted;
    }

    public function isInboxMessage()
    {
        return $this->type == self::TYPE_INBOX;
    }


    public function setAsDeleted()
    {
        if ($this->isMine()) {
            if (empty($this->message->message) && empty($this->message->title)) {
                if ($this->message->delete()) {
                    return 2;
                }
            }
            if ($this->updateAttributes(['deleted' => true])) {
                return 1;
            }
        }

        if ($this->type == self::TYPE_INBOX) {
            if ($this->updateAttributes(['deleted' => true])) {
                return 1;
            }
        }
    }

    public function setAsRestored()
    {
        return $this->updateAttributes(['deleted' => false]);
    }

    public function isMine()
    {
        return $this->type == AdminMessageItem::TYPE_OUTBOX || $this->type == AdminMessageItem::TYPE_DRAFT;
    }

    public function getViewUrl()
    {
        return linkTo(['message/my-messages', 'id' => $this->id, 'folder' => $this->type]);
    }

    public function getReplyUrl()
    {
        return linkTo(['message/compose', 'reply' => $this->id]);
    }
}
