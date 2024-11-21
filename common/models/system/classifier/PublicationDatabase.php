<?php

namespace common\models\system\classifier;

class PublicationDatabase extends _BaseClassifier
{
    const PUBLICATION_DATABASE_OTHER = '10';
    public static function tableName()
    {
        return 'h_publication_database';
    }
}