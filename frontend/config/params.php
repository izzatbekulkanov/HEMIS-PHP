<?php
return [
    'adminEmail' => 'admin@example.com',
    'studentMenu' => [
        'education' => [
            'icon' => 'book',
            'label' => 'Curriculum',
            'url' => 'education/time-table',
            'items' => [
                'education/curriculum' => [
                    'label' => 'Curriculum Curriculum',
                    'url' => 'education/curriculum',
                    'home' => true,
                    'icon' => 'curriculum',
                    'enable' => false,
                ],
                'education/time-table' => [
                    'label' => 'Lessons Timetable',
                    'url' => 'education/time-table',
                    'home' => true,
                    'icon' => 'timetable',
                    'enable' => true,
                ],
                'education/exam-table' => [
                    'label' => 'Exams Timetable',
                    'url' => 'education/exam-table',
                    'home' => true,
                    'icon' => 'exams',
                    'enable' => true,
                ],
                'education/subjects' => [
                    'label' => 'Education Subjects',
                    'url' => 'education/subjects',
                    'home' => true,
                    'icon' => 'resources',
                    'enable' => false,
                ],
                'education/attendance' => [
                    'label' => 'My Attendance',
                    'url' => 'education/attendance',
                    'home' => true,
                    'icon' => 'attendance',
                    'enable' => false,
                ],
                'education/performance' => [
                    'label' => 'My Performance',
                    'url' => 'education/performance',
                    'home' => true,
                    'icon' => 'performance',
                    'enable' => false,
                ],
                'test/exams' => [
                    'label' => 'Test Exams',
                    'url' => 'test/exams',
                    'home' => true,
                    'icon' => '26',
                    'enable' => true,
                ],
                'education/academic-data' => [
                    'label' => 'Academic Data',
                    'url' => 'education/academic-data',
                    'home' => true,
                    'icon' => 'curriculum',
                    'enable' => false,
                ],
            ],
        ],
        'student' => [
            'icon' => 'user',
            'label' => 'Student Data',
            'url' => 'student/decree',
            'items' => [
                'student/decree' => [
                    'label' => 'Student Decree',
                    'url' => 'student/decree',
                    'enable' => false,
                    'home' => true,
                    'icon' => 'documents',
                ],
                'student/contract' => [
                    'label' => 'Contract',
                    'url' => 'student/contract',
                    'enable' => false,
                    'home' => true,
                    'icon' => 'contract',
                ],
                'student/reference' => [
                    'label' => 'Reference',
                    'url' => 'student/reference',
                    'enable' => false,
                    'home' => true,
                    'icon' => 'contract',
                ],
                'student/document' => [
                    'label' => 'Documents',
                    'url' => 'student/document',
                    'enable' => false,
                    'home' => true,
                    'icon' => 'contract',
                ],
                'student/graduate-qualifying' => [
                    'label' => 'Documents',
                    'url' => 'student/graduate-qualifying',
                    'enable' => false,
                    'home' => true,
                    'icon' => 'curriculum',
                ],

                /*
                'student/subject-choose' => [
                    'label' => 'Fan tanlash',
                    'url' => 'student/subject-choose',
                    'enable' => false,
                    'home' => true,
                    'icon' => 'subject-choose',
                ],
                'student/personal-data' => [
                    'label' => 'Personal Data',
                    'url' => 'student/personal-data',
                    'enable' => false,
                    'home' => true,
                    'icon' => 'profile',
                ],
                'student/stipend' => [
                    'label' => 'Stipend',
                    'url' => 'student/stipend',
                    'enable' => false,
                    'home' => true,
                    'icon' => 'stipend',
                ],

                'student/penalty' => [
                    'label' => 'Penalty',
                    'url' => 'student/penalty',
                    'enable' => false,
                    'home' => true,
                    'icon' => 'penalty',
                ],
                'student/contract' => [
                    'label' => 'Contract',
                    'url' => 'student/contract',
                    'enable' => false,
                    'home' => true,
                    'icon' => 'contract',
                ],*/
            ],
        ],
        'message' => [
            'icon' => 'envelope',
            'label' => 'Messages',
            'url' => 'message/my-messages',
            'items' => [
                'message/my-messages' => [
                    'label' => 'My Messages',
                    'url' => 'message/my-messages',
                    'enable' => false,
                    'home' => false,
                    'icon' => 'messages',
                ],
                'message/compose' => [
                    'label' => 'Compose',
                    'url' => 'message/compose',
                    'enable' => false,
                ],
            ],
        ],
        'system' => [
            'icon' => 'gear',
            'label' => 'System',
            'url' => 'dashboard/profile',
            'items' => [
                'dashboard/profile' => [
                    'label' => 'My Profile',
                    'url' => 'dashboard/profile',
                    'enable' => true,
                ],
                'dashboard/logins' => [
                    'label' => 'Login History',
                    'url' => 'dashboard/logins',
                    'enable' => true,
                ],
            ],
        ],
    ],
];
