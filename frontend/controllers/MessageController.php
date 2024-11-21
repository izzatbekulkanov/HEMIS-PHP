<?php

namespace frontend\controllers;

use common\models\system\Admin;
use common\models\system\AdminMessage;

use common\models\system\AdminMessageItem;
use common\models\system\Contact;
use frontend\models\system\Student;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class MessageController extends FrontendController
{
    public $activeMenu = 'message';

    public function actionMyMessages($id = false, $folder = 'inbox')
    {
        $this->layout = 'mail';
        $user = $this->_user();

        if ($id) {
            if ($model = $this->findMessageModelItem($id, $user)) {

                if ($this->get('remove')) {
                    if ($model->delete()) {
                        if ($model->message->sender->id == $user->contact->id)
                            $model->message->updateAttributes(['status' => AdminMessage::STATUS_DELETED]);

                        $this->addSuccess(__('Message "{title}" deleted permanently', ['title' => $model->message->getShortTitle()]));
                    }
                    return $this->redirect(['my-messages', 'folder' => AdminMessageItem::TYPE_TRASH]);
                }

                if ($this->get('delete')) {
                    if ($result = $model->setAsDeleted()) {
                        if ($result == 2) {
                            $this->addSuccess(__('Message "{title}" deleted permanently', ['title' => $model->message->getShortTitle()]));
                            return $this->redirect(['my-messages', 'folder' => $folder]);
                        } else {
                            $this->addSuccess(__('Message "{title}" moved to trash', ['title' => $model->message->getShortTitle()]));
                        }
                    }
                    return $this->redirect(['my-messages', 'folder' => $folder]);
                }

                if ($this->get('restore')) {
                    if ($model->setAsRestored()) {
                        $this->addSuccess(__('Message "{title}" restored', ['title' => $model->message->getShortTitle()]));
                    }

                    if ($model->type == AdminMessageItem::TYPE_DRAFT) {
                        return $this->redirect(['compose', 'id' => $model->_message]);
                    }

                    if ($model->type == AdminMessageItem::TYPE_OUTBOX) {
                        return $this->redirect(['my-messages', 'folder' => AdminMessageItem::TYPE_OUTBOX]);
                    }

                    if ($model->type == AdminMessageItem::TYPE_INBOX) {
                        return $this->redirect(['my-messages', 'folder' => AdminMessageItem::TYPE_INBOX]);
                    }
                }

                if (!$model->opened) {
                    $model->updateAttributes(['opened' => true]);
                }

                return $this->renderView(['model' => $model, 'folder' => $folder,]);
            }
        }

        if ($this->get('clean')) {
            if ($count = AdminMessageItem::cleanTrashMessages($user)) {
                $this->addSuccess(__('{count} messages removed from trash', ['count' => $count]));
            }
            return $this->redirect(['my-messages', 'folder' => 'trash']);
        }

        $searchModel = new AdminMessageItem();

        return $this->renderView([
            'dataProvider' => $searchModel->searchMessages($this->getFilterParams(), $folder, $this->_user()),
            'searchModel' => $searchModel,
            'folder' => $folder,
        ]);
    }

    /**
     * @skipAccess
     */
    public function actionCompose($id = false)
    {
        $user = $this->_user();
        $this->layout = 'mail';

        $contacts = $this->get('contacts');
        if ($contacts === 'json') {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $searchModel = new Contact();

            return $searchModel->searchForAdmin(['Contact' => ['search' => $this->get('query')]], $this->_user(), false);
        } else if ($contacts === 'html') {
            $searchModel = new Contact();

            return $this->renderAjax("contacts", [
                'searchModel' => $searchModel,
            ]);
        }

        if ($id) {
            if ($model = $this->findMessageModel($id, $user)) {

                if ($this->get('autosave')) {
                    if ($model->load($this->post())) {
                        return $model->save(false);
                    }
                }

                if ($model->load($this->post())) {
                    $action = $this->post('action', 'draft');

                    if ($action == 'draft') {
                        if ($model->saveAsDraft()) {
                            $this->addSuccess(__('Message "{title}" saved as draft', ['title' => $model->getShortTitle()]));
                            return $this->redirect(['message/my-messages', 'folder' => AdminMessageItem::TYPE_DRAFT]);
                        }
                    }

                    if ($action == 'send') {
                        if ($count = $model->sendMessage()) {
                            $this->addSuccess(__('Message "{title}" sent to {count} address', ['title' => $model->getShortTitle(), 'count' => $count]));
                            return $this->redirect(['message/my-messages', 'folder' => AdminMessageItem::TYPE_OUTBOX]);
                        } else {
                            $errors = $model->getFirstErrors();
                            $this->addError(array_pop($errors), true);
                        }
                    }
                }

                return $this->renderView([
                    'model' => $model,
                ]);
            }
        } else {
            if ($forward = $this->get('forward')) {
                if ($message = $this->findMessageModelItem($forward, $user)) {
                    if ($model = AdminMessage::createDraftMessage($user, $message->message, null)) {
                        return $this->redirect(['compose', 'id' => $model->id]);
                    }
                }
                return $this->goBack();
            }

            if ($reply = $this->get('reply')) {
                if ($message = $this->findMessageModelItem($reply, $user)) {
                    if ($model = AdminMessage::createDraftMessage($user, null, $message->message)) {
                        return $this->redirect(['compose', 'id' => $model->id]);
                    }
                }
                return $this->goBack();
            }

            if ($model = AdminMessage::createDraftMessage($user, null, null, $this->get('title'))) {
                return $this->redirect(['compose', 'id' => $model->id]);
            }
            $this->addError(__('Could not create draft message'));
        }

        return $this->goBack();
    }

    /**
     * @param $id
     * @return AdminMessage
     * @throws NotFoundHttpException
     */
    protected function findMessageModel($id, Student $admin = null)
    {
        if (($model = AdminMessage::findOne($id)) !== null) {
            if ($admin && $admin->contact->id != $model->_sender) {
                $this->notFoundException();
            }

            return $model;
        } else {
            $this->notFoundException();
        }
    }

    /**
     * @param $id
     * @return AdminMessageItem
     * @throws NotFoundHttpException
     */
    protected function findMessageModelItem($id, Student $admin)
    {
        /**
         * @var $model AdminMessageItem
         */
        if (($model = AdminMessageItem::findOne($id)) !== null) {
            if ($admin->contact->id == $model->_sender || $admin->contact->id == $model->_recipient) {
                return $model;
            }

            $this->notFoundException();
        } else {
            $this->notFoundException();
        }
    }

}
