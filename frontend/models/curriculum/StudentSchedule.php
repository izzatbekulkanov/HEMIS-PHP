<?php


namespace frontend\models\curriculum;


use common\models\curriculum\ECurriculumWeek;
use common\models\curriculum\EducationYear;
use common\models\curriculum\EStudentSubject;
use common\models\curriculum\ESubjectExamSchedule;
use common\models\curriculum\ESubjectSchedule;
use common\models\curriculum\Semester;
use common\models\system\classifier\EducationWeekType;
use frontend\models\system\Student;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

class StudentSchedule extends ESubjectSchedule
{

    //@todo joriy semestrni olish cherez curriculum orqali, h_semester da
    //Dars jadvalida va davomatlarda training_type ni chiqarish
    //semestr boshlanishi va tugashi bor
    //Mening davomatim e_attendance da bor. Semestr tanlanadi. Qoldirilgan kundagi mavzusini ham chiqarish kera
    //va summarni grafiklarni ham qoshish kerak. Fanlar boyicha obshi summarni soat hamda sababli sababzilarni
    //chiqarish kerak. Summa soatlar oralig'iga tushish, yani qoldirilgan soatlar chegarasiga asosan rangni ajratib
    //ko'rsatish
    //fanlar kesimida umumiy qoldirilgan soatlar hisoblanib chiqariladi.
    //Soat ustiga bosganda qaysi kunlari kiritilganligi chiqariladi
    //Mening baholarim e_performance jadvalida nazorat turlari bo'yicha tushadi

    //Shaxsiy malumotlarda talabaning barcha anketa malumotlari chiqariladi
    /**
     *
     * Semester tanlanadi, fanlar jadvali chiqariladi, va fanda
     *
     * O'quv rejada talaba qaysi o'quv rejadaligi va semestrlar kesimida
     * qaysi fanlarni o'qishi chiqishi kerak. O'qib yakunlanganlarining bahosi chiqish
     * kerak (e_academic_record).
     *
     * @param Student $student
     * @param $params
     * @return ArrayDataProvider
     */
    public static function searchForStudentWeekly(Student $student, Semester $semester, $week)
    {

        /**
         * @var $item self
         */
        $result = [];
        $subjects = EStudentSubject::find()
            ->select(['_subject'])
            ->where([
                'active' => self::STATUS_ENABLE,
                '_curriculum' => $student->meta->_curriculum,
                '_semester' => $semester->code,
                '_student' => $student->meta->_student
            ])
            //->groupBy(['_subject'])
            ->column();

        $query = self::find()
            ->leftJoin('e_subject', 'e_subject.id=_subject')
            ->leftJoin('e_employee', 'e_employee.id=_employee')
            ->with(['group', 'semester', 'group', 'subject', 'lessonPair', 'auditorium', 'employee', 'trainingType'])
            ->orderBy(['lesson_date' => SORT_ASC, '_lesson_pair' => SORT_ASC])
            ->andFilterWhere(['_semester' => $semester->code, '_week' => $week, '_group'=>$student->meta->_group])
            ->andFilterWhere(['e_subject_schedule._subject' => $subjects]);

        foreach ($query->all() as $item) {
            if (!isset($result[$item->lesson_date->getTimestamp()])) {
                $result[$item->lesson_date->getTimestamp()] = [
                    'date' => $item->lesson_date,
                    'items' => [],
                ];
            }
            $result[$item->lesson_date->getTimestamp()]['items'][] = $item;
        }

        return new ArrayDataProvider([
            'allModels' => array_values($result),
            'pagination' => [
                'pageSize' => 6,
            ],
        ]);
    }

    public static function getStudentSemesterWeeks(Student $student, Semester $semester)
    {
        $items = ECurriculumWeek::find()
            ->with(['educationWeekType'])
            ->where([
                '_curriculum' => $student->meta->_curriculum,
                '_semester' => $semester->code,
                'active' => true,
                '_education_week_type' => EducationWeekType::EDUCATION_WEEK_TYPE_THEORETICAL,
            ])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->all();
        $keys = array_keys($items);
        return [
            'keys' => $keys,
            'options' => ArrayHelper::map($items, 'id', function (ECurriculumWeek $item) {
                return sprintf(
                    "%s. %s / %s",
                    $item->position,
                    \Yii::$app->formatter->asDate($item->start_date->getTimestamp(), 'php: d F'),
                    \Yii::$app->formatter->asDate($item->end_date->getTimestamp(), 'php: d F')
                );
            }),
        ];
    }

    public function searchForStudent(Student $student, $semester)
    {
        $query = self::find()
            ->leftJoin('e_subject', 'e_subject.id=_subject')
            ->leftJoin('e_employee', 'e_employee.id=_employee')
            ->with(['group', 'semester', 'group', 'subject', 'lessonPair', 'auditorium', 'employee']);

        if ($this->search) {
            $query->orWhereLike('e_subject.name', $this->search);
            $query->orWhereLike('e_employee.first_name', $this->search);
            $query->orWhereLike('e_employee.second_name', $this->search);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['lesson_date' => SORT_DESC, '_lesson_pair' => SORT_ASC],
                'attributes' => [
                    '_curriculum',
                    '_education_year',
                    '_semester',
                    'lesson_date',
                    '_lesson_pair',
                    '_group',
                    'position',
                    'updated_at',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => 6,
            ],
        ]);

        $query->andFilterWhere([
            '_group' => $student->getGroupIds(),
        ]);

        if ($this->_subject) {
            $query->andFilterWhere(['_subject' => $this->_subject]);
        }

        if ($semester) {
            //$query->andFilterWhere(['_semester' => $semester]);
        }

        return $dataProvider;
    }

    public function getFormattedDate()
    {
        return \Yii::$app->formatter->asDate($this->lesson_date->getTimestamp(), 'php:D, d-F');
    }

}