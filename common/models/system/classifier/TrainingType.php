<?php

namespace common\models\system\classifier;

class TrainingType extends _BaseClassifier
{
    const TRAINING_TYPE_LECTURE = '11';
    const TRAINING_TYPE_LABORATORY = '12';
    const TRAINING_TYPE_PRACTICE = '13';
    const TRAINING_TYPE_SEMINAR = '14';
    const TRAINING_TYPE_TRAINING = '15';
    const TRAINING_TYPE_COURSE_WORK = '16';
    const TRAINING_TYPE_INDEPENDENT = '17';
	public static function tableName()
    {
        return 'h_training_type';
    }
}