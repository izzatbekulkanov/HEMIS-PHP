<?php


namespace frontend\models\curriculum;


use common\models\curriculum\Semester;
use frontend\models\system\Student;

class StudentSemester extends Semester
{
    /**
     * @param Student $student
     * @return Semester[]
     */
    public static function getForStudent(Student $student)
    {
        $curriculum = $student->meta->curriculum;
        $items = Semester::find()
            ->where([
                '_curriculum' => $curriculum->id,
                'active' => true,
            ])
            ->orderBy(['position' => SORT_ASC])
            ->all();

        return $items;
    }
}