<?php
return [
    'backendMenu' => [
        'structure' => [
            'icon' => 'university',
            'label' => 'Structure of HEI',
            'url' => 'structure/university',
            'items' => [
                'structure/about-university' => [
                    'label' => 'About HEI',
                    'url' => 'structure/university',
                    'icon' => 'info-circle',
                ],
                'structure/faculty' => [
                    'label' => 'Faculty',
                    'url' => 'structure/faculty',
                    'icon' => 'building',
                ],
                'structure/department' => [
                    'label' => 'Department',
                    'url' => 'structure/department',
                    'icon' => 'suitcase',
                ],
                'structure/section' => [
                    'label' => 'Section',
                    'url' => 'structure/section',
                    'icon' => 'suitcase',
                ]

            ],
        ],

        'employee' => [
            'icon' => 'briefcase',
            'label' => 'Employee Information',
            'url' => 'employee/employee',
            'items' => [
                'employee/employee' => [
                    'label' => 'Employee',
                    'url' => 'employee/employee',
                ],
                'employee/direction' => [
                    'label' => 'Direction',
                    'url' => 'employee/direction',
                ],
                'employee/teacher' => [
                    'label' => 'Teacher',
                    'url' => 'employee/teacher',
                ],
                'employee/professional-development' => [
                    'label' => 'Professional Development',
                    'url' => 'employee/professional-development',
                ],
                'employee/competition' => [
                    'label' => 'Competition',
                    'url' => 'employee/competition',
                ],
                'employee/status' => [
                    'label' => 'Employee Status',
                    'url' => 'employee/employee-status',
                ],
                'employee/academic-degree' => [
                    'label' => 'Employee Academic Degree',
                    'url' => 'employee/academic-degree',
                ],
                'employee/foreign-training' => [
                    'label' => 'Employee Foreign Training',
                    'url' => 'employee/foreign-training',
                ],
                'employee/foreign-employee' => [
                    'label' => 'Employee Foreign Employee',
                    'url' => 'employee/foreign-employee',
                ],
                'employee/tutor-group' => [
                    'label' => 'Employee Tutor Group',
                    'url' => 'employee/tutor-group',
                ]
            ],
        ],

        'student' => [
            'icon' => 'users',
            'label' => 'Student Information',
            'url' => 'student/about-university',
            'items' => [
                'student/special' => [
                    'label' => 'Special',
                    'url' => 'student/special',
                ],
                'student/student' => [
                    'label' => 'Student',
                    'url' => 'student/student',
                ],
                'student/group' => [
                    'label' => 'Group',
                    'url' => 'student/group',
                ],
                /*'student/sup-group' => [
                    'label' => 'SubGroup',
                    'url' => 'student/sup-group',
                ],*/

                'student/student-fixed' => [
                    'label' => 'Student Fixed',
                    'url' => 'student/student-fixed',
                ],
                'student/student-contingent' => [
                    'label' => 'Contingent',
                    'url' => 'student/student-contingent',
                ],
                'student/admission-quota' => [
                    'label' => 'Admission Quota',
                    'url' => 'student/admission-quota',
                ],
                'student/student-award' => [
                    'label' => 'Student Award',
                    'url' => 'student/student-award',
                ],
                'student/qualification' => [
                    'label' => 'Qualification',
                    'url' => 'student/qualification',
                ],
                'student/exchange' => [
                    'label' => 'Student Exchange',
                    'url' => 'student/exchange',
                ],
                'student/olympiad' => [
                    'label' => 'Student Olympiad',
                    'url' => 'student/olympiad',
                ],
                'student/sport' => [
                    'label' => 'Student Sport',
                    'url' => 'student/sport',
                ],
            ],
        ],

        'transfer' => [
            'icon' => 'user',
            'label' => 'Student Status',
            'url' => '#',
            'items' => [
                'decree/index' => [
                    'label' => 'Decrees',
                    'url' => 'decree/index',
                ],
                'transfer/student-transfer' => [
                    'label' => 'Transfer',
                    'url' => 'transfer/student-transfer',
                ],
                'transfer/student-group' => [
                    'label' => 'Transfer Student Group',
                    'url' => 'transfer/student-group',
                ],
                'transfer/student-course-transfer' => [
                    'label' => 'Transfer (Course)',
                    'url' => 'transfer/student-course-transfer',
                ],
                'transfer/student-course-expel' => [
                    'label' => 'Expel (Course / Semester)',
                    'url' => 'transfer/student-course-expel',
                ],
                'transfer/student-expel' => [
                    'label' => 'Expel',
                    'url' => 'transfer/student-expel',
                ],
                'transfer/academic-leave' => [
                    'label' => 'Academic leave',
                    'url' => 'transfer/academic-leave',
                ],
                'transfer/restore' => [
                    'label' => 'Transfer Restore',
                    'url' => 'transfer/restore',
                ],
                'transfer/return' => [
                    'label' => 'Transfer Return',
                    'url' => 'transfer/return',
                ],
                'transfer/graduate' => [
                    'label' => 'Graduate',
                    'url' => 'transfer/graduate',
                ],
                'transfer/graduate-simple' => [
                    'label' => 'Graduate Simple',
                    'url' => 'transfer/graduate-simple',
                ],
                'transfer/status' => [
                    'label' => 'Transfer Status',
                    'url' => 'transfer/status',
                ],
            ],
        ],

        'subjects' => [
            'icon' => 'database',
            'label' => 'Subjects',
            'url' => '#',
            'items' => [
                'curriculum/subject-group' => [
                    'label' => 'Subject Groups',
                    'url' => 'curriculum/subject-group',
                ],
                'curriculum/subject' => [
                    'label' => 'Subject',
                    'url' => 'curriculum/subject',
                ],
                'teacher/subject-topics' => [
                    'label' => 'Subject Topics',
                    'url' => 'teacher/subject-topics',
                ],
                'teacher/subject-resources' => [
                    'label' => 'Subject Resources',
                    'url' => 'teacher/subject-resources',
                ],
                'teacher/subject-tasks' => [
                    'label' => 'Subject Tasks',
                    'url' => 'teacher/subject-tasks',
                ],
                'teacher/calendar-plan' => [
                    'label' => 'Calendar Plan',
                    'url' => 'teacher/calendar-plan',
                ],
            ],
        ],

        'curriculum' => [
            'icon' => 'book',
            'label' => 'Curriculum',
            'url' => 'curriculum/education-year',
            'items' => [
                'curriculum/education-year' => [
                    'label' => 'Education Year',
                    'url' => 'curriculum/education-year',
                ],
                'curriculum/curriculum' => [
                    'label' => 'Curriculum',
                    'url' => 'curriculum/curriculum',
                ],
                'curriculum/semester' => [
                    'label' => 'Education Semester',
                    'url' => 'curriculum/semester',
                ],
                'curriculum/curriculum-block' => [
                    'label' => 'Subject Block',
                    'url' => 'curriculum/curriculum-block',
                ],
                'curriculum/student-register' => [
                    'label' => 'Student Register',
                    'url' => 'curriculum/student-register',
                ],
                'curriculum/schedule' => [
                    'label' => 'Schedule',
                    'url' => 'curriculum/schedule-info',
                ],
                'curriculum/schedule-view' => [
                    'label' => 'Schedule View',
                    'url' => 'curriculum/schedule-info-view',
                ],
                'curriculum/exam-schedule' => [
                    'label' => 'Exam Schedule',
                    'url' => 'curriculum/exam-schedule-info',
                ],
                'curriculum/exam-schedule-view' => [
                    'label' => 'Exam Schedule View',
                    'url' => 'curriculum/exam-schedule-info-view',
                ],
                'curriculum/marking-system' => [
                    'label' => 'Marking System',
                    'url' => 'curriculum/marking-system',
                ],
                'curriculum/grade-type' => [
                    'label' => 'Grade Type',
                    'url' => 'curriculum/grade-type',
                ],
                'curriculum/rating-grade' => [
                    'label' => 'Rating Grade',
                    'url' => 'curriculum/rating-grade',
                ],
                'curriculum/lesson-pair' => [
                    'label' => 'Lesson Pair',
                    'url' => 'curriculum/lesson-pair',
                ],
                'exam/index' => [
                    'label' => 'Exams',
                    'url' => 'exam/index',
                ],
            ],
        ],

        'attendance' => [
            'icon' => 'edit',
            'label' => 'Attendance',
            'url' => 'attendance/attendance-journal',
            'items' => [
                'attendance/attendance-journal' => [
                    'label' => 'Journal',
                    'url' => 'attendance/attendance-journal',
                ],
                'attendance/activity' => [
                    'label' => 'Attendance Activity ',
                    'url' => 'attendance/activity',
                ],
                'attendance/report' => [
                    'label' => 'Attendance Report ',
                    'url' => 'attendance/report',
                ],
                'attendance/overall' => [
                    'label' => 'Overall',
                    'url' => 'attendance/overall',
                ],
                'attendance/by-subjects' => [
                    'label' => 'Subjects',
                    'url' => 'attendance/by-subjects',
                ],
                'attendance/attendance-setting' => [
                    'label' => 'Setting',
                    'url' => 'attendance/attendance-setting',
                ],
                'attendance/lessons' => [
                    'label' => 'Attendance Lessons',
                    'url' => 'attendance/lessons',
                ],
                /*'attendance/lessons-stat' => [
                    'label' => 'Attendance Lessons Stat',
                    'url' => 'attendance/lessons-stat',
                ],*/
            ],
        ],

        'performance' => [
            'icon' => 'check',
            'label' => 'Performance',
            'url' => 'performance/performance',
            'items' => [
                'performance/performance' => [
                    'label' => 'Performance',
                    'url' => 'performance/performance',
                ],
                'performance/summary' => [
                    'label' => 'Summary',
                    'url' => 'performance/summary',
                ],
                'performance/debtors' => [
                    'label' => 'Debtors',
                    'url' => 'performance/debtors',
                ],
                'performance/gpa' => [
                    'label' => 'Performance Gpa',
                    'url' => 'performance/gpa',
                ],
                'performance/ptt' => [
                    'label' => 'Performance Ptt',
                    'url' => 'performance/ptt',
                ],
            ],
        ],

        'infrastructure' => [
            'icon' => 'building',
            'label' => 'Infrastructure',
            'url' => 'infrastructure/building',
            'items' => [
                'infrastructure/building' => [
                    'label' => 'Building',
                    'url' => 'infrastructure/building',
                ],
                'infrastructure/auditorium' => [
                    'label' => 'Auditorium',
                    'url' => 'infrastructure/auditorium',
                ],
            ],
        ],

        'teacher-attendance' => [
            'icon' => 'calendar-check-o ',
            'label' => 'Trainings',
            'url' => 'teacher/time-table',
            'items' => [
                'teacher/time-table' => [
                    'label' => 'My Timetable',
                    'url' => 'teacher/time-table',
                ],
                'teacher/training-list' => [
                    'label' => 'Training List',
                    'url' => 'teacher/training-list',
                ],
                'teacher/attendance-journal' => [
                    'label' => 'Attendance Journal',
                    'url' => 'teacher/attendance-journal',
                ],
                'teacher/rating-journal' => [
                    'label' => 'Rating Journal',
                    'url' => 'teacher/rating-journal',
                ],
            ],
        ],
        'teacher-examtable' => [
            'icon' => 'hourglass-2',
            'label' => 'Rating Grades',
            'url' => 'teacher/midterm-exam-table',
            'items' => [
                'teacher/midterm-exam-table' => [
                    'label' => 'Midterm Examtable',
                    'url' => 'teacher/midterm-exam-table',
                ],
                'teacher/final-exam-table' => [
                    'label' => 'Final Examtable',
                    'url' => 'teacher/final-exam-table',
                ],

                'teacher/other-exam-table' => [
                    'label' => 'Other Examtable',
                    'url' => 'teacher/other-exam-table',
                ],
                'teacher/certificate-committee-result' => [
                    'label' => 'Certificate Committee Result',
                    'url' => 'teacher/certificate-committee-result',
                ],
                /*'teacher/performance-journal' => [
                    'label' => 'Performance Journal',
                    'url' => 'teacher/performance-journal',
                ],*/
            ],

        ],
        'archive' => [
            'icon' => 'archive',
            'label' => 'Archive',
            'url' => '#',
            'items' => [
                'archive/academic-record' => [
                    'label' => 'Academic record',
                    'url' => 'archive/academic-record',
                ],
                'archive/diploma' => [
                    'label' => 'Diploma Registration',
                    'url' => 'archive/diploma',
                ],
                'archive/diploma-blank' => [
                    'label' => 'Diploma Blank',
                    'url' => 'archive/diploma-blank',
                ],
                'archive/diploma-list' => [
                    'label' => 'Diploma List',
                    'url' => 'archive/diploma-list',
                ],
                'archive/transcript' => [
                    'label' => 'Academic Information',
                    'url' => 'archive/transcript',
                ],
                'archive/academic-information-data' => [
                    'label' => 'Academic Information Data',
                    'url' => 'archive/academic-information-data',
                ],
                'archive/accreditation' => [
                    'label' => 'Accreditation',
                    'url' => 'archive/accreditation',
                ],
                'archive/batch-rate' => [
                    'label' => 'Batch Rate',
                    'url' => 'archive/batch-rate',
                ],
                'archive/employment' => [
                    'label' => 'Graduate Employment',
                    'url' => 'archive/employment',
                ],
                'archive/certificate-committee' => [
                    'label' => 'Certificate Committee',
                    'url' => 'archive/certificate-committee',
                ],
                'archive/graduate-work' => [
                    'label' => 'Graduate Work',
                    'url' => 'archive/graduate-work',
                ],
                'archive/academic-sheet' => [
                    'label' => 'Archive Academic Sheet',
                    'url' => 'archive/academic-sheet',
                ],
                'archive/reference' => [
                    'label' => 'Archive Reference',
                    'url' => 'archive/reference',
                ],
            ],
        ],
        'science' => [
            'icon' => 'globe',
            'label' => 'Science',
            'url' => '#',
            'items' => [
                'science/project' => [
                    'label' => 'Science Project',
                    'url' => 'science/project',
                ],
                'science/publication-methodical' => [
                    'label' => 'Science Methodical Publication',
                    'url' => 'science/publication-methodical',
                ],
                'science/publication-scientifical' => [
                    'label' => 'Science Publication Scientifical',
                    'url' => 'science/publication-scientifical',
                ],
                'science/publication-property' => [
                    'label' => 'Science Publication Property',
                    'url' => 'science/publication-property',
                ],
                'science/scientific-activity' => [
                    'label' => 'Science Scientific Activity',
                    'url' => 'science/scientific-activity',
                ],
                'science/publication-methodical-check' => [
                    'label' => 'Science Methodical Check',
                    'url' => 'science/publication-methodical-check',
                ],
                'science/publication-scientifical-check' => [
                    'label' => 'Science Scientifical Check',
                    'url' => 'science/publication-scientifical-check',
                ],
                'science/publication-property-check' => [
                    'label' => 'Science Property Check',
                    'url' => 'science/publication-property-check',
                ],
                'science/scientific-activity-check' => [
                    'label' => 'Science Activity Check',
                    'url' => 'science/scientific-activity-check',
                ],
            ],
        ],
        'rating' => [
            'icon' => 'line-chart',
            'label' => 'Rating',
            'url' => '#',
            'items' => [
                'science/criteria-template' => [
                    'label' => 'Rating Criteria Template',
                    'url' => 'science/criteria-template',
                ],
                'science/publication-criteria' => [
                    'label' => 'Science Publication Criteria',
                    'url' => 'science/publication-criteria',
                ],
                'science/scientific-activity-criteria' => [
                    'label' => 'Science Scientific Activity',
                    'url' => 'science/scientific-activity-criteria',
                ],
                'science/teacher-rating' => [
                    'label' => 'Rating Teacher',
                    'url' => 'science/teacher-rating',
                ],
                'science/department-rating' => [
                    'label' => 'Rating Department',
                    'url' => 'science/department-rating',
                ],
                'science/faculty-rating' => [
                    'label' => 'Rating Faculty',
                    'url' => 'science/faculty-rating',
                ],
            ],
        ],
        'doctorate' => [
            'icon' => 'graduation-cap',
            'label' => 'Doctorate',
            'url' => '#',
            'items' => [
                'science/doctorate-specialty' => [
                    'label' => 'Doctorate Specialty',
                    'url' => 'science/specialty',
                ],
                'science/doctorate-student' => [
                    'label' => 'Doctorate Student',
                    'url' => 'science/doctorate-student',
                ],
            ],
        ],

        'finance' => [
            'icon' => 'envelope',
            'label' => 'Finance',
            'url' => '#',
            'items' => [
                'finance/minimum-wage' => [
                    'label' => 'Minimum Wage',
                    'url' => 'finance/minimum-wage',
                ],
                'finance/scholarship-amount' => [
                    'label' => 'Amount of Scholarship',
                    'url' => 'finance/scholarship-amount',
                ],
                'finance/contract-type' => [
                    'label' => 'Type of Contract',
                    'url' => 'finance/contract-type',
                ],
                'finance/contract-price' => [
                    'label' => 'Contract Price',
                    'url' => 'finance/contract-price',
                ],
                'finance/contract-price-foreign' => [
                    'label' => ' Foreign Contract Price',
                    'url' => 'finance/contract-price-foreign',
                ],
                'finance/increased-contract-coef' => [
                    'label' => 'Coefficient of Increased Contract',
                    'url' => 'finance/increased-contract-coef',
                ],
                'finance/uzasbo-data' => [
                    'label' => 'Student Uzasbo',
                    'url' => 'finance/uzasbo-data',
                ],
                'finance/student-contract' => [
                    'label' => 'Student Contract',
                    'url' => 'finance/student-contract',
                ],
                'finance/payment-monitoring' => [
                    'label' => 'Payment monitoring',
                    'url' => 'finance/payment-monitoring',
                ],
                'finance/control-contract' => [
                    'label' => 'Control Contract',
                    'url' => 'finance/control-contract',
                ],
                'finance/student-contract-manual' => [
                    'label' => 'Student Contract Manual',
                    'url' => 'finance/student-contract-manual',
                ],
                'finance/contract-invoice' => [
                    'label' => 'Contract Invoice',
                    'url' => 'finance/contract-invoice',
                ],
                'finance/payment-monitoring-department' => [
                    'label' => 'Payment Monitoring Department',
                    'url' => 'finance/payment-monitoring-department',
                ],
                'finance/scholarship' => [
                    'label' => 'Scholarship',
                    'url' => 'finance/scholarship',
                ]
            ],
        ],
        'statistical' => [
            'icon' => 'bar-chart',
            'label' => 'Statistical',
            'url' => '#',
            'items' => [
                'statistical/by-student' => [
                    'label' => 'By Student',
                    'url' => 'statistical/by-student',
                ],
                'statistical/by-student-general' => [
                    'label' => 'By Student General',
                    'url' => 'statistical/by-student-general',
                ],
                'statistical/by-student-social' => [
                    'label' => 'By Student Social',
                    'url' => 'statistical/by-student-social',
                ],
                'statistical/by-teacher' => [
                    'label' => 'By Teacher',
                    'url' => 'statistical/by-teacher',
                ],
                'statistical/by-resource' => [
                    'label' => 'By Resources',
                    'url' => 'statistical/by-resource',
                ],
                'statistical/by-contract' => [
                    'label' => 'By Contract',
                    'url' => 'statistical/by-contract',
                ],
                'statistical/by-employment' => [
                    'label' => 'By Employment',
                    'url' => 'statistical/by-employment',
                ],
                /*'statistical/by-group' => [
                    'label' => 'By Group',
                    'url' => 'statistical/by-group',
                ],
                'statistical/by-nationality' => [
                    'label' => 'By Nationality',
                    'url' => 'statistical/by-nationality',
                ],
                'statistical/by-education-type' => [
                    'label' => 'By Education Type',
                    'url' => 'statistical/by-education-type',
                ],
                'statistical/by-accommodation' => [
                    'label' => 'By Accommodation',
                    'url' => 'statistical/by-accommodation',
                ],
                'statistical/by-education-form' => [
                    'label' => 'By Education Form',
                    'url' => 'statistical/by-education-form',
                ],*/
            ],
        ],
        'report' => [
            'icon' => 'bars',
            'label' => 'Report',
            'url' => '#',
            'items' => [
                'report/by-teachers' => [
                    'label' => 'By Teachers',
                    'url' => 'report/by-teachers',
                ],
                'report/by-students' => [
                    'label' => 'By Students',
                    'url' => 'report/by-students',
                ],
                'report/by-resources' => [
                    'label' => 'By Theme Resources',
                    'url' => 'report/by-resources',
                ],
                'report/by-rooms' => [
                    'label' => 'Report By Rooms',
                    'url' => 'report/by-rooms',
                ],
                'report/teacher-map' => [
                    'label' => 'Report Teacher Map',
                    'url' => 'report/teacher-map',
                ],
            ],
        ],
        'message' => [
            'icon' => 'envelope',
            'label' => 'Messages',
            'url' => 'message/my-messages',
            'items' => [
                'message/index' => [
                    'label' => 'All Messages',
                    'url' => 'message/all-messages',
                ],
                'message/my-messages' => [
                    'label' => 'My Messages',
                    'url' => 'message/my-messages',
                ],
                'message/compose' => [
                    'label' => 'Compose',
                    'url' => 'message/compose',
                ],
            ],
        ],


        'system' => [
            'icon' => 'gear',
            'label' => 'System',
            'url' => 'system/index',
            'items' => [
                'system/admin' => [
                    'label' => 'Administrators',
                    'url' => 'system/admin',
                ],
                'system/role' => [
                    'label' => 'Administrator Roles',
                    'url' => 'system/role',
                ],
                'system/login' => [
                    'label' => 'Login History',
                    'url' => 'system/login',
                ],
                'system/system-log' => [
                    'label' => 'System Logs',
                    'url' => 'system/system-log',
                ],
                'system/sync-log' => [
                    'label' => 'Sync Logs',
                    'url' => 'system/sync-log',
                ],
                'system/sync-status' => [
                    'label' => 'Sync Status',
                    'url' => 'system/sync-status',
                ],
                'system/translation' => [
                    'label' => 'UI Translation',
                    'url' => 'system/translation',
                ],
                'system/configuration' => [
                    'label' => 'Configuration',
                    'url' => 'system/configuration',
                ],
                'system/classifier' => [
                    'label' => 'Classifiers',
                    'url' => 'system/classifier',
                ],
                'system/backup' => [
                    'label' => 'Backups',
                    'url' => 'system/backup',
                ]
            ],
        ],
    ],
];
