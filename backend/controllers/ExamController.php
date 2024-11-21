<?php

namespace backend\controllers;

use common\components\Config;
use common\models\curriculum\ECurriculumSubject;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EExam;
use common\models\curriculum\EExamExclude;
use common\models\curriculum\EExamGroup;
use common\models\curriculum\EExamQuestion;
use common\models\curriculum\EExamStudent;
use common\models\curriculum\EExamStudentMetaResult;
use common\models\FormImportQuestion;
use common\models\student\EGroup;
use common\models\system\classifier\_BaseClassifier;
use common\models\system\SystemClassifier;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class ExamController extends BackendController
{
    public $activeMenu = 'curriculum';

    public function actionIndex($id = false)
    {
        $searchModel = new EExam();
        $dataProvider = $searchModel->searchForEmployee($this->getFilterParams(), $this->_user());
        if ($searchModel->_education_year) {
            $searchModel->_education_year = $searchModel->_education_year;
            $dataProvider->query->andFilterWhere(['e_exam._education_year' => $searchModel->_education_year]);
        } else {
            $searchModel->_education_year = EducationYear::getCurrentYear()->code;
            $dataProvider->query->andFilterWhere(['e_exam._education_year' => EducationYear::getCurrentYear()->code]);
        }
        return $this->renderView([
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function importQuestions(EExam $exam)
    {
        $model = new FormImportQuestion();

        $key = 'TEST_QUESTIONS_EXAM_' . $exam->id;


        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->session->set($key, $model->content);

            if ($this->post('import', false)) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $count = 0;
                    $pos = $exam->getTestQuestions()->count();
                    foreach ($model->normalizedContent() as $i => $item) {
                        $question = new EExamQuestion(
                            [
                                'content' => $item['content'],
                                '_exam' => $exam->id,
                                'position' => $pos + $i,
                                'active' => true,
                            ]
                        );
                        if ($question->save()) {
                            $count++;
                        }
                    }
                    if ($count) {
                        $transaction->commit();
                        $this->addSuccess(__('{count} questions imported', ['count' => $count]));
                        Yii::$app->session->offsetUnset($key);

                        return $this->redirect(['exam/edit', 'id' => $exam->id, 'questions' => 1]);
                    } else {
                        if (isset($question)) {
                            if ($error = $question->getOneError()) {
                                $this->addError($error);
                            }
                        }
                    }
                    $transaction->rollBack();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    $this->addError($e->getMessage());
                }
                return $this->refresh();
            }
        } else {
            if ($this->isGet()) {
                if ($content = Yii::$app->session->get($key)) {
                    $model->content = $content;
                }
            }
        }

        return $this->render('questions-import', [
            'model' => $exam,
            'importModel' => $model,
        ]);
    }

    public function actionEdit($id = false)
    {
        if ($id) {
            $model = $this->findExam($id);
            if (!($this->_user()->role->isSuperAdminRole() || $this->_user()->role->isAcademic())) {
                if ($this->_user()->_employee !== $model->_employee) {
                    return $this->redirect(['exam/index']);
                }
            }
        } else {
            $model = new EExam([
               // '_education_year' => EducationYear::getCurrentYear()->code
            ]);
        }

        $searchModel = new EGroup();

        if ($g = $this->get('group_start_at')) {
            if ($groupModel = EExamGroup::findOne(['_group' => $g, '_exam' => $model->id])) {
                if (array_key_exists('date', $_GET)) {
                    if ($groupModel->setStartAtDate($this->get('date'))) {
                        $this->addSuccess(__('{group} guruhi uchun imtihon boshlanish sanasi yangilandi', ['group' => $groupModel->group->name]));
                    }
                    return 1;
                }
                return $this->renderAjax('_groups_date', [
                    'model' => $groupModel,
                    'attribute' => 'start_at',
                ]);
            }
        }

        if ($g = $this->get('group_finish_at')) {
            if ($groupModel = EExamGroup::findOne(['_group' => $g, '_exam' => $model->id])) {
                if (array_key_exists('date', $_GET)) {
                    if ($groupModel->setFinishAtDate($this->get('date'))) {
                        $this->addSuccess(__('{group} guruhi uchun imtihon tugash sanasi yangilandi', ['group' => $groupModel->group->name]));
                    }
                    return 1;
                }
                return $this->renderAjax('_groups_date', [
                    'model' => $groupModel,
                    'attribute' => 'finish_at',
                ]);
            }
        }

        if ($q = $this->get('q')) {
            if ($question = EExamQuestion::findOne(['id' => $q, '_exam' => $model->id])) {
                if ($this->get('delete')) {
                    if ($message = $question->anyIssueWithDelete()) {
                        $this->addError($message);
                    } else {
                        $this->addSuccess(
                            __('Question `{title}` deleted successfully.', [
                                'title' => $model->name
                            ])
                        );
                        return $this->redirect(['edit', 'id' => $model->id, 'questions' => 1]);
                    }
                    return $this->redirect(['edit', 'id' => $model->id, 'q' => $question->id]);
                }

                if ($question->load($this->post()) && $question->save()) {
                    $this->addSuccess(__('Question `{title}` updated successfully', ['title' => $question->getShortTitle()]));

                    return $this->redirect(['edit', 'id' => $model->id, 'q' => $question->id]);
                }

                return $this->render('questions-edit', [
                    'model' => $model,
                    'question' => $question,
                ]);
            }

            return $this->redirect(['edit', 'id' => $model->id]);
        }

        if ($this->get('questions')) {

            if ($model->canEditExam() && $this->get('import')) {
                return $this->importQuestions($model);
            }

            if ($this->get('export')) {
                $contents = array_map(function ($q) {
                    return $q->content;
                }, $model->testQuestions);
                $filename = $model->name;
                $filename = str_replace('/', '-', $filename . '_questions.txt');
                $dir = Yii::getAlias('@runtime/q');
                FileHelper::createDirectory($dir);
                @file_put_contents($dir . DS . $filename, implode("\n+++++\n", $contents));
                Yii::$app->response->sendFile($dir . DS . $filename);
                FileHelper::unlink($dir . DS . $filename);
                return;
            }

            return $this->render('questions', [
                'model' => $model,
            ]);
        }

        if ($this->get('results')) {

            if ($item = $this->get('item')) {
                /**
                 * @var $item EExamStudent
                 */
                if ($item = EExamStudent::findOne(['id' => $item, '_exam' => $model->id])) {
                    if ($item->isFinished()) {
                        return $this->render('results_item', [
                            'model' => $item,
                        ]);
                    } else {
                        $this->addError(__('Ushbu talaba testi yakunlanmagan'));
                        return $this->redirect(currentTo(['item' => null]));
                    }
                }
            }
            $searchModel = new EExamStudent();

            if ($this->get('download')) {
                /**
                 * @var $models EExamStudent[]
                 */
                $models = $searchModel->searchByExam($model, $this->getFilterParams(), false);
                if (count($models)) {
                    $item = $models[0];

                    $headerStyle = [
                        'font' => [
                            'bold' => true,
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ],
                    ];


                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();
                    $sheet->setTitle(__('Exam Results'));

                    $row = 1;
                    $col = 1;

                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->getAttributeLabel('_exam'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->getAttributeLabel('_student'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->getAttributeLabel('_group'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->getAttributeLabel('ip'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->getAttributeLabel('attempts'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->getAttributeLabel('correct'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->getAttributeLabel('mark'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->getAttributeLabel('percent'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->getAttributeLabel('started_at'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->getAttributeLabel('time'), DataType::TYPE_STRING);
                    $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->getAttributeLabel('finished_at'), DataType::TYPE_STRING);

                    foreach ($models as $item) {
                        $row++;
                        $col = 1;
                        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->exam->name, DataType::TYPE_STRING);
                        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->student->getFullName(), DataType::TYPE_STRING);
                        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->group->name, DataType::TYPE_STRING);
                        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->ip, DataType::TYPE_STRING);
                        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->attempts, DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->correct, DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->mark, DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, round($item->percent, 0), DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->started_at ? Yii::$app->formatter->asDatetime($item->started_at->getTimestamp(), 'php: d.m.Y H:i') : '', DataType::TYPE_STRING);
                        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, __('{min} daqiqa', ['min' => ceil($item->time / 60)]), DataType::TYPE_STRING);
                        $sheet->setCellValueExplicitByColumnAndRow($col++, $row, $item->finished_at ? Yii::$app->formatter->asDatetime($item->finished_at->getTimestamp(), 'php: d.m.Y H:i') : '', DataType::TYPE_STRING);
                    }
                    $sheet->getColumnDimension('A')->setWidth(30);
                    $sheet->getColumnDimension('B')->setWidth(40);
                    $sheet->getColumnDimension('C')->setWidth(30);
                    $sheet->getColumnDimension('D')->setWidth(10);
                    $sheet->getColumnDimension('E')->setWidth(10);
                    $sheet->getColumnDimension('F')->setWidth(10);
                    $sheet->getColumnDimension('G')->setWidth(10);
                    $sheet->getColumnDimension('H')->setWidth(10);
                    $sheet->getColumnDimension('I')->setWidth(30);
                    $sheet->getColumnDimension('J')->setWidth(10);
                    $sheet->getColumnDimension('K')->setWidth(30);
                    $sheet->getStyle('A1:L1')
                        ->applyFromArray($headerStyle)
                        ->getAlignment()->setWrapText(true);

                    $name = __('Exam Results') . '-' . Yii::$app->formatter->asDatetime(time(), 'php:d_m_Y_h_i_s') . '.xlsx';
                    $writer = new Xlsx($spreadsheet);
                    $fileName = Yii::getAlias('@runtime') . DS . $name;
                    $writer->save($fileName);

                    return Yii::$app->response->sendFile($fileName, $name);
                }
            }


            return $this->render('results', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $searchModel->searchByExam($model, $this->getFilterParams()),
            ]);
        }

        if ($this->get('add')) {
            $items = explode(',', $this->get('items'));
            if ($count = $model->addGroups($items)) {
                $this->addSuccess(__('Imtihonga {count} ta guruh biriktirildi', ['count' => count($items)]));
            }
            return 1;
        }

        if ($this->get('remove')) {
            $items = explode(',', $this->get('items'));
            if ($count = $model->removeGroups($items)) {
                $this->addSuccess(__('Imtihondan {count} ta guruh olindi', ['count' => $count]));
            }
        }

        if ($this->get('groups')) {
            return $this->renderAjax("_groups", [
                'searchModel' => $searchModel,
                'model' => $model,
            ]);
        }

        if ($group = $this->get('students')) {
            if ($items = $this->post('items')) {
                EExamExclude::deleteAll(['_exam' => $model->id, '_student' => $items]);
                if ($this->post('state') == 0) {
                    $rows = [];
                    foreach ($items as $itemId) {
                        $rows[] = [
                            '_exam' => $model->id,
                            '_student' => $itemId,
                        ];
                    }
                    if (count($rows)) {
                        return Yii::$app->db
                            ->createCommand()
                            ->batchInsert(EExamExclude::tableName(), array_keys($rows[0]), $rows)
                            ->execute();
                    }
                }
                return 1;
            }
            if ($student = $this->get('exclude')) {
                $params = ['_exam' => $model->id, '_student' => $student];
                if ($exclude = EExamExclude::findOne($params)) {
                    return $exclude->delete();
                } else {
                    return (new EExamExclude($params))->save();
                }
            }

            $studentSearch = new EExamStudentMetaResult();

            return $this->renderAjax("_students", [
                'dataProvider' => $studentSearch->searchForStudent($model, $group),
                'model' => $model,
            ]);
        }

        if ($this->get('reset') && $model->id) {
            if ($count = EExamStudent::updateAll(['session' => null], "session is not null and _exam=:exam", ['exam' => $model->id])) {
                $this->addSuccess(__("{count} test sessions released from lock", ['count' => $count]));
            } else {
                $this->addInfo(__('Aktiv seanslar mavjud emas'));
            }
            return $this->redirect(['edit', 'id' => $model->id]);
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->_semester = ECurriculumSubject::getByCurriculumSubject($model->_curriculum, $model->_subject)->_semester;
            if ($model->isNewRecord)
                $model->_employee = $this->_user()->_employee;

            if ($model->save()) {
                $this->addSuccess(
                    __($id ? 'Exam `{name}` updated successfully.' : 'Exam `{name}` created successfully.', [
                        'name' => $model->name
                    ])
                );
                return $this->redirect(['edit', 'id' => $model->id]);
            } else {
                $this->addError($model->getOneError());
            }
        }

        return $this->renderView([
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findExam($id);

        if ($message = $model->anyIssueWithDelete()) {
            $this->addError($message);
            return $this->redirect(['edit', 'id' => $model->id]);
        } else {
            $this->addSuccess(
                __('Exam `{name}` deleted successfully.', [
                    'name' => $model->name
                ])
            );
        }


        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return EExam
     * @throws NotFoundHttpException
     */
    protected function findExam($id)
    {
        if (($model = EExam::findOne($id)) !== null) {
            return $model;
        } else {
            $this->notFoundException();
        }
    }
}
