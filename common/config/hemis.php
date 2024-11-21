<?php

return [
    'class' => \common\components\hemis\HemisApi::class,
    'endpointUrl' => getenv('HEMIS_ENDPOINT'),
    'syncModels' => [
        [
            'class' => \common\models\structure\EDepartment::class,
            'name' => 'Department',
            'info' => 'University Departments, Sections and Faculties',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\student\EStudent::class,
            'name' => 'Student',
            'info' => 'Student and student related academic data',
            'syncCheck' => true,
        ],

        [
            'class' => \common\models\employee\EEmployee::class,
            'name' => 'Employee',
            'info' => 'Employee and employee related data',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\employee\EEmployeeMeta::class,
            'name' => 'Position',
            'info' => 'Employee Positions',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\employee\EEmployeeAcademicDegree::class,
            'name' => 'Academic Degrees',
            'info' => 'Employee Academic Degree',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\employee\EEmployeeTraining::class,
            'name' => 'Foreign Trainings',
            'info' => 'Employee Foreign Training',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\student\EStudentExchange::class,
            'name' => 'Student Exchange',
            'info' => 'Exchange Program Students Data',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\student\EStudentOlympiad::class,
            'name' => 'Student Olympiad',
            'info' => 'Student Olympiad Data',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\archive\EStudentEmployment::class,
            'name' => 'Archive Employment',
            'info' => 'Student Employment Data',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\student\EStudentSport::class,
            'name' => 'Student Sport',
            'info' => 'Student Sport Data',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\science\EDoctorateStudent::class,
            'name' => 'Doctorate Student',
            'info' => 'Doctorate Student related data',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\science\EDissertationDefense::class,
            'name' => 'Dissertation Defense',
            'info' => 'Doctoral dissertation defense records',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\science\EProject::class,
            'name' => 'Projects',
            'info' => 'Projects Information',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\science\EProjectExecutor::class,
            'name' => 'Project Executors',
            'info' => 'Project Executors Information',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\science\EProjectMeta::class,
            'name' => 'Project Finance',
            'info' => 'Project Finance Information',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\science\EPublicationMethodical::class,
            'name' => 'Publication Methodical',
            'info' => 'Methodical Publications and Authors',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\science\EPublicationScientific::class,
            'name' => 'Publication Scientific',
            'info' => 'Scientific Publications and Authors',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\science\EPublicationProperty::class,
            'name' => 'Publication Property',
            'info' => 'Property Publications and Authors',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\science\EScientificPlatformProfile::class,
            'name' => 'Science Scientific Activity',
            'info' => 'Teachers Scientific Activity',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\archive\EDiplomaBlank::class,
            'name' => 'Diploma Blank',
            'info' => 'Student diploma blanks',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\archive\EStudentDiploma::class,
            'name' => 'Diploma',
            'info' => 'Student diploma and academic records',
            'syncCheck' => true,
        ],
        [
            'class' => \common\models\system\SystemClassifier::class,
            'name' => 'Classifiers',
            'info' => 'Classifiers and classifier options',
            'syncCheck' => false,
        ],
        [
            'class' => \common\models\structure\EUniversity::class,
            'name' => 'University',
            'info' => 'University Data',
            'syncCheck' => false,
        ],
        [
            'class' => \common\models\report\ReportContract::class,
            'name' => 'Report: Contracts',
            'info' => 'Student Contract daily statistics',
            'syncCheck' => false,
        ],
        [
            'class' => \common\models\report\ReportEmployment::class,
            'name' => 'Report: Employment',
            'info' => 'Student employment annual statistics',
            'syncCheck' => false,
        ],
    ]
];
