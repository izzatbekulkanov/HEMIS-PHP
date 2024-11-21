<?php

namespace common\models\system\classifier;

use yii\db\Expression;
use yii\helpers\ArrayHelper;

class ExamType extends _BaseClassifier
{
    const EXAM_TYPE_CURRENT = '11';
    const EXAM_TYPE_MIDTERM = '12';
    const EXAM_TYPE_LIMIT = '10'; //EXAM_TYPE_CURRENT + EXAM_TYPE_MIDTERM
    
	const EXAM_TYPE_FINAL = '13';
    const EXAM_TYPE_OVERALL = '14';

    const EXAM_TYPE_CURRENT_FIRST = '15';
    const EXAM_TYPE_CURRENT_SECOND = '16';
    const EXAM_TYPE_MIDTERM_FIRST = '17';
    const EXAM_TYPE_MIDTERM_SECOND = '18';

    const  EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_FIRST = '111';
    const  EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_SECOND = '112';
    const  EXAM_TYPE_CURRENT_FINAL_EXAM_TYPE_THIRD = '113';

    const  EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_FIRST = '121';
    const  EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_SECOND = '122';
    const  EXAM_TYPE_MIDTERM_FINAL_EXAM_TYPE_THIRD = '123';

    const  EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_FIRST = '101';
    const  EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_SECOND = '102';
    const  EXAM_TYPE_LIMIT_FINAL_EXAM_TYPE_THIRD = '103';

    const  EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_FIRST = '131';
    const  EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_SECOND = '132';
    const  EXAM_TYPE_FINAL_FINAL_EXAM_TYPE_THIRD = '133';

    const  EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_FIRST = '141';
    const  EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_SECOND = '142';
    const  EXAM_TYPE_OVERALL_FINAL_EXAM_TYPE_THIRD = '143';

    public static function tableName()
    {
        return 'h_exam_type';
    }
	
    public static function getParentClassifierOptions()
    {
        $items = self::find()
            ->andFilterWhere(['active' => true])
			->andFilterWhere(['in', 'code', [self::EXAM_TYPE_CURRENT, self::EXAM_TYPE_MIDTERM, self::EXAM_TYPE_FINAL, self::EXAM_TYPE_OVERALL]])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }

    public static function getChildrenOption($parent=false)
    {
        $items = self::find()
            ->andFilterWhere(['_parent'=>$parent, 'active' => true])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return $items;
    }

    public static function getClassifierDefinedOptions($codes)
    {
        $items = self::find()
            ->where(['active' => true])
            ->andWhere(['not in', 'code', $codes])
            ->andWhere(['<>', 'code', ExamType::EXAM_TYPE_OVERALL])
            ->orderBy(['position' => SORT_ASC, 'code' => SORT_ASC])
            ->all();

        return ArrayHelper::map($items, 'code', 'name');
    }
}