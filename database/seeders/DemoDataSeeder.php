<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const PASSWORD = 'Password123!';

    public function run(): void
    {
        $now = now();

        DB::table('organization_invites')->delete();
        DB::table('organization_user_permissions')->delete();
        DB::table('organization_user')->delete();
        DB::table('interview_user')->delete();
        DB::table('interviews')->delete();
        DB::table('application_answers')->delete();
        DB::table('documents')->delete();
        DB::table('applications')->delete();
        DB::table('job_positions')->delete();
        DB::table('template_fields')->delete();
        DB::table('application_templates')->delete();
        DB::table('organizations')->delete();
        DB::table('users')->delete();

        $northstarChairmanId = $this->createUser(
            name: 'Eleanor Bishop',
            role: 'chairman',
            email: 'chairman@northstar.test',
            verifiedAt: $now
        );

        $marcusId = $this->createUser(
            name: 'Marcus Reed',
            role: 'interviewer',
            email: 'marcus@northstar.test',
            verifiedAt: $now
        );

        $priyaId = $this->createUser(
            name: 'Priya Shah',
            role: 'interviewer',
            email: 'priya@northstar.test',
            verifiedAt: $now
        );

        $ninaId = $this->createUser(
            name: 'Nina Owens',
            role: 'interviewer',
            email: 'nina@northstar.test',
            verifiedAt: $now
        );

        $bluepeakChairmanId = $this->createUser(
            name: 'Adrian Cole',
            role: 'chairman',
            email: 'chairman@bluepeak.test',
            verifiedAt: $now
        );

        $theoId = $this->createUser(
            name: 'Theo Hart',
            role: 'interviewer',
            email: 'theo@bluepeak.test',
            verifiedAt: $now
        );

        $northstarOrgId = DB::table('organizations')->insertGetId([
            'chairman_id' => $northstarChairmanId,
            'name' => 'Northstar Hiring Committee',
            'join_code' => 'NORTHSTAR2026',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $bluepeakOrgId = DB::table('organizations')->insertGetId([
            'chairman_id' => $bluepeakChairmanId,
            'name' => 'BluePeak Faculty Search',
            'join_code' => 'BLUEPEAK2026',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->attachUsersToOrganization($northstarOrgId, [
            $northstarChairmanId,
            $marcusId,
            $priyaId,
            $ninaId,
        ], $now);

        $this->attachUsersToOrganization($bluepeakOrgId, [
            $bluepeakChairmanId,
            $theoId,
            $priyaId,
        ], $now);

        $permissionIds = DB::table('permissions')->orderBy('id')->pluck('id')->all();

        $firstHalfCount = max(1, (int) ceil(count($permissionIds) / 2));
        $firstHalfPermissionIds = array_slice($permissionIds, 0, $firstHalfCount);
        $firstTwoPermissionIds = array_slice($permissionIds, 0, min(2, count($permissionIds)));
        $lastTwoPermissionIds = array_slice(
            $permissionIds,
            max(0, count($permissionIds) - min(2, count($permissionIds)))
        );

        $permissionRows = array_merge(
            $this->buildPermissionRows($northstarOrgId, $northstarChairmanId, $permissionIds, $northstarChairmanId, $now),
            $this->buildPermissionRows($northstarOrgId, $marcusId, $firstHalfPermissionIds, $northstarChairmanId, $now),
            $this->buildPermissionRows($northstarOrgId, $priyaId, $firstTwoPermissionIds, $northstarChairmanId, $now),
            $this->buildPermissionRows($bluepeakOrgId, $bluepeakChairmanId, $permissionIds, $bluepeakChairmanId, $now),
            $this->buildPermissionRows($bluepeakOrgId, $theoId, $firstTwoPermissionIds, $bluepeakChairmanId, $now),
            $this->buildPermissionRows($bluepeakOrgId, $priyaId, $lastTwoPermissionIds, $bluepeakChairmanId, $now)
        );

        if (!empty($permissionRows)) {
            DB::table('organization_user_permissions')->insert($permissionRows);
        }

        $facultyTemplateId = DB::table('application_templates')->insertGetId([
            'organization_id' => $northstarOrgId,
            'created_by' => $northstarChairmanId,
            'name' => 'Faculty Candidate Application',
            'request_name' => true,
            'request_email' => true,
            'request_phone' => true,
            'request_resume' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $staffTemplateId = DB::table('application_templates')->insertGetId([
            'organization_id' => $northstarOrgId,
            'created_by' => $ninaId,
            'name' => 'Student Staff Application',
            'request_name' => true,
            'request_email' => true,
            'request_phone' => false,
            'request_resume' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $researchTemplateId = DB::table('application_templates')->insertGetId([
            'organization_id' => $bluepeakOrgId,
            'created_by' => $bluepeakChairmanId,
            'name' => 'Research Assistant Application',
            'request_name' => true,
            'request_email' => true,
            'request_phone' => true,
            'request_resume' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $facultyFieldIds = [
            'full_name' => DB::table('template_fields')->insertGetId([
                'template_id' => $facultyTemplateId,
                'label' => 'Full Legal Name',
                'type' => 'text',
                'options' => null,
                'required' => true,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'cover_letter' => DB::table('template_fields')->insertGetId([
                'template_id' => $facultyTemplateId,
                'label' => 'Cover Letter',
                'type' => 'textarea',
                'options' => null,
                'required' => true,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'highest_degree' => DB::table('template_fields')->insertGetId([
                'template_id' => $facultyTemplateId,
                'label' => 'Highest Degree',
                'type' => 'select',
                'options' => json_encode(['M.S.', 'Ph.D.', 'Ed.D.']),
                'required' => true,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'teaching_areas' => DB::table('template_fields')->insertGetId([
                'template_id' => $facultyTemplateId,
                'label' => 'Teaching Areas',
                'type' => 'checkbox',
                'options' => json_encode(['Algorithms', 'Databases', 'Networks', 'Software Engineering']),
                'required' => false,
                'order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'work_authorization' => DB::table('template_fields')->insertGetId([
                'template_id' => $facultyTemplateId,
                'label' => 'Work Authorization',
                'type' => 'radio',
                'options' => json_encode(['Authorized to work', 'Requires sponsorship']),
                'required' => true,
                'order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'cv' => DB::table('template_fields')->insertGetId([
                'template_id' => $facultyTemplateId,
                'label' => 'Curriculum Vitae',
                'type' => 'file',
                'options' => null,
                'required' => true,
                'order' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'start_date' => DB::table('template_fields')->insertGetId([
                'template_id' => $facultyTemplateId,
                'label' => 'Available Start Date',
                'type' => 'date',
                'options' => null,
                'required' => true,
                'order' => 7,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
        ];

        $staffFieldIds = [
            'student_id' => DB::table('template_fields')->insertGetId([
                'template_id' => $staffTemplateId,
                'label' => 'Student ID',
                'type' => 'text',
                'options' => null,
                'required' => true,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'interest' => DB::table('template_fields')->insertGetId([
                'template_id' => $staffTemplateId,
                'label' => 'Why do you want this role?',
                'type' => 'textarea',
                'options' => null,
                'required' => true,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'class_standing' => DB::table('template_fields')->insertGetId([
                'template_id' => $staffTemplateId,
                'label' => 'Class Standing',
                'type' => 'select',
                'options' => json_encode(['Freshman', 'Sophomore', 'Junior', 'Senior']),
                'required' => true,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
        ];

        $researchFieldIds = [
            'major' => DB::table('template_fields')->insertGetId([
                'template_id' => $researchTemplateId,
                'label' => 'Major',
                'type' => 'text',
                'options' => null,
                'required' => true,
                'order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'research_interests' => DB::table('template_fields')->insertGetId([
                'template_id' => $researchTemplateId,
                'label' => 'Research Interests',
                'type' => 'textarea',
                'options' => null,
                'required' => true,
                'order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'preferred_lab' => DB::table('template_fields')->insertGetId([
                'template_id' => $researchTemplateId,
                'label' => 'Preferred Lab',
                'type' => 'select',
                'options' => json_encode(['AI Lab', 'Systems Lab', 'Security Lab']),
                'required' => true,
                'order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'prior_research' => DB::table('template_fields')->insertGetId([
                'template_id' => $researchTemplateId,
                'label' => 'Prior Research Experience',
                'type' => 'radio',
                'options' => json_encode(['Yes', 'No']),
                'required' => true,
                'order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'transcript' => DB::table('template_fields')->insertGetId([
                'template_id' => $researchTemplateId,
                'label' => 'Transcript',
                'type' => 'file',
                'options' => null,
                'required' => true,
                'order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'start_date' => DB::table('template_fields')->insertGetId([
                'template_id' => $researchTemplateId,
                'label' => 'Available Start Date',
                'type' => 'date',
                'options' => null,
                'required' => true,
                'order' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
        ];

        $assistantProfessorJobId = DB::table('job_positions')->insertGetId([
            'organization_id' => $northstarOrgId,
            'template_id' => $facultyTemplateId,
            'created_by' => $northstarChairmanId,
            'title' => 'Assistant Professor of Computer Science',
            'description' => 'Teach undergraduate and graduate computer science courses.',
            'requirements' => 'Ph.D. in Computer Science or a related field.',
            'status' => 'open',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $labAssistantJobId = DB::table('job_positions')->insertGetId([
            'organization_id' => $northstarOrgId,
            'template_id' => $staffTemplateId,
            'created_by' => $ninaId,
            'title' => 'Computer Lab Assistant',
            'description' => 'Support students and maintain the department computer lab.',
            'requirements' => 'Strong communication and basic troubleshooting skills.',
            'status' => 'closed',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $researchAssistantJobId = DB::table('job_positions')->insertGetId([
            'organization_id' => $bluepeakOrgId,
            'template_id' => $researchTemplateId,
            'created_by' => $bluepeakChairmanId,
            'title' => 'Undergraduate Research Assistant',
            'description' => 'Assist with lab experiments, coding, and literature review.',
            'requirements' => 'Coursework in programming and interest in research.',
            'status' => 'open',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $sofiaApplicationId = DB::table('applications')->insertGetId([
            'job_position_id' => $assistantProfessorJobId,
            'template_id' => $facultyTemplateId,
            'applicant_name' => 'Sofia Martinez',
            'applicant_email' => 'sofia.martinez@example.com',
            'applicant_phone' => '555-0100',
            'status' => 'submitted',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $danielApplicationId = DB::table('applications')->insertGetId([
            'job_position_id' => $assistantProfessorJobId,
            'template_id' => $facultyTemplateId,
            'applicant_name' => 'Daniel Kim',
            'applicant_email' => 'daniel.kim@example.com',
            'applicant_phone' => '555-0101',
            'status' => 'under_review',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $rebeccaApplicationId = DB::table('applications')->insertGetId([
            'job_position_id' => $assistantProfessorJobId,
            'template_id' => $facultyTemplateId,
            'applicant_name' => 'Rebecca Stone',
            'applicant_email' => 'rebecca.stone@example.com',
            'applicant_phone' => '555-0102',
            'status' => 'no_longer_under_consideration',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $omarApplicationId = DB::table('applications')->insertGetId([
            'job_position_id' => $labAssistantJobId,
            'template_id' => $staffTemplateId,
            'applicant_name' => 'Omar Patel',
            'applicant_email' => 'omar.patel@example.com',
            'applicant_phone' => null,
            'status' => 'withdrawn',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $ethanApplicationId = DB::table('applications')->insertGetId([
            'job_position_id' => $researchAssistantJobId,
            'template_id' => $researchTemplateId,
            'applicant_name' => 'Ethan Brooks',
            'applicant_email' => 'ethan.brooks@example.com',
            'applicant_phone' => '555-0104',
            'status' => 'under_review',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $sofiaCvDocumentId = $this->createDocument(
            $sofiaApplicationId,
            'sofia-martinez-cv.pdf',
            'documents/applications/sofia-martinez-cv.pdf',
            'application/pdf',
            $now
        );

        $this->createDocument(
            $sofiaApplicationId,
            'sofia-martinez-teaching-statement.pdf',
            'documents/applications/sofia-martinez-teaching-statement.pdf',
            'application/pdf',
            $now
        );

        $danielCvDocumentId = $this->createDocument(
            $danielApplicationId,
            'daniel-kim-cv.pdf',
            'documents/applications/daniel-kim-cv.pdf',
            'application/pdf',
            $now
        );

        $this->createDocument(
            $danielApplicationId,
            'daniel-kim-research-statement.pdf',
            'documents/applications/daniel-kim-research-statement.pdf',
            'application/pdf',
            $now
        );

        $rebeccaCvDocumentId = $this->createDocument(
            $rebeccaApplicationId,
            'rebecca-stone-cv.pdf',
            'documents/applications/rebecca-stone-cv.pdf',
            'application/pdf',
            $now
        );

        $this->createDocument(
            $omarApplicationId,
            'omar-patel-resume.pdf',
            'documents/applications/omar-patel-resume.pdf',
            'application/pdf',
            $now
        );

        $ethanTranscriptDocumentId = $this->createDocument(
            $ethanApplicationId,
            'ethan-brooks-transcript.pdf',
            'documents/applications/ethan-brooks-transcript.pdf',
            'application/pdf',
            $now
        );

        $this->createDocument(
            $ethanApplicationId,
            'ethan-brooks-resume.pdf',
            'documents/applications/ethan-brooks-resume.pdf',
            'application/pdf',
            $now
        );

        $this->insertApplicationAnswers($sofiaApplicationId, [
            $facultyFieldIds['full_name'] => [
                'value' => 'Sofia Martinez',
            ],
            $facultyFieldIds['cover_letter'] => [
                'value' => 'I am excited to contribute to undergraduate teaching and curriculum development.',
            ],
            $facultyFieldIds['highest_degree'] => [
                'value' => 'Ph.D.',
            ],
            $facultyFieldIds['teaching_areas'] => [
                'value' => json_encode(['Algorithms', 'Software Engineering']),
            ],
            $facultyFieldIds['work_authorization'] => [
                'value' => 'Authorized to work',
            ],
            $facultyFieldIds['cv'] => [
                'value' => 'documents/applications/sofia-martinez-cv.pdf',
                'document_id' => $sofiaCvDocumentId,
            ],
            $facultyFieldIds['start_date'] => [
                'value' => '2026-08-15',
            ],
        ], $now);

        $this->insertApplicationAnswers($danielApplicationId, [
            $facultyFieldIds['full_name'] => [
                'value' => 'Daniel Kim',
            ],
            $facultyFieldIds['cover_letter'] => [
                'value' => 'My background in distributed systems and teaching makes me a strong fit for this role.',
            ],
            $facultyFieldIds['highest_degree'] => [
                'value' => 'Ph.D.',
            ],
            $facultyFieldIds['teaching_areas'] => [
                'value' => json_encode(['Databases', 'Networks']),
            ],
            $facultyFieldIds['work_authorization'] => [
                'value' => 'Requires sponsorship',
            ],
            $facultyFieldIds['cv'] => [
                'value' => 'documents/applications/daniel-kim-cv.pdf',
                'document_id' => $danielCvDocumentId,
            ],
            $facultyFieldIds['start_date'] => [
                'value' => '2026-08-01',
            ],
        ], $now);

        $this->insertApplicationAnswers($rebeccaApplicationId, [
            $facultyFieldIds['full_name'] => [
                'value' => 'Rebecca Stone',
            ],
            $facultyFieldIds['cover_letter'] => [
                'value' => 'I am interested in joining a collaborative department with strong teaching values.',
            ],
            $facultyFieldIds['highest_degree'] => [
                'value' => 'Ed.D.',
            ],
            $facultyFieldIds['teaching_areas'] => [
                'value' => json_encode(['Software Engineering']),
            ],
            $facultyFieldIds['work_authorization'] => [
                'value' => 'Authorized to work',
            ],
            $facultyFieldIds['cv'] => [
                'value' => 'documents/applications/rebecca-stone-cv.pdf',
                'document_id' => $rebeccaCvDocumentId,
            ],
            $facultyFieldIds['start_date'] => [
                'value' => '2026-09-01',
            ],
        ], $now);

        $this->insertApplicationAnswers($omarApplicationId, [
            $staffFieldIds['student_id'] => [
                'value' => 'S1029384',
            ],
            $staffFieldIds['interest'] => [
                'value' => 'I enjoy helping other students and maintaining lab spaces.',
            ],
            $staffFieldIds['class_standing'] => [
                'value' => 'Junior',
            ],
        ], $now);

        $this->insertApplicationAnswers($ethanApplicationId, [
            $researchFieldIds['major'] => [
                'value' => 'Computer Science',
            ],
            $researchFieldIds['research_interests'] => [
                'value' => 'Machine learning, human-computer interaction, and evaluation methods.',
            ],
            $researchFieldIds['preferred_lab'] => [
                'value' => 'AI Lab',
            ],
            $researchFieldIds['prior_research'] => [
                'value' => 'Yes',
            ],
            $researchFieldIds['transcript'] => [
                'value' => 'documents/applications/ethan-brooks-transcript.pdf',
                'document_id' => $ethanTranscriptDocumentId,
            ],
            $researchFieldIds['start_date'] => [
                'value' => '2026-06-01',
            ],
        ], $now);

        // Int 1
        $int1 = DB::table('interviews')->insertGetId([
            'application_id' => $danielApplicationId,
            'scheduled_at' => $now->copy()->addDays(7),
            'status' => 'scheduled',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('interview_user')->insert([
            'interview_id' => $int1,
            'user_id' => $marcusId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Int 2
        $int2 = DB::table('interviews')->insertGetId([
            'application_id' => $rebeccaApplicationId,
            'scheduled_at' => $now->copy()->subDays(3),
            'status' => 'canceled',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('interview_user')->insert([
            'interview_id' => $int2,
            'user_id' => $priyaId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Int 3
        $int3 = DB::table('interviews')->insertGetId([
            'application_id' => $ethanApplicationId,
            'scheduled_at' => $now->copy()->subDays(1),
            'status' => 'completed',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('interview_user')->insert([
            'interview_id' => $int3,
            'user_id' => $theoId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('organization_invites')->insert([
            [
                'organization_id' => $northstarOrgId,
                'created_by' => $northstarChairmanId,
                'code' => 'NORTHSTAR-GENERAL',
                'email' => null,
                'used' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organization_id' => $northstarOrgId,
                'created_by' => $northstarChairmanId,
                'code' => 'NORTHSTAR-PRIYA',
                'email' => 'priya@northstar.test',
                'used' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organization_id' => $bluepeakOrgId,
                'created_by' => $bluepeakChairmanId,
                'code' => 'BLUEPEAK-THEO',
                'email' => 'theo@bluepeak.test',
                'used' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    private function createUser(string $name, string $role, string $email, $verifiedAt): int
    {
        return DB::table('users')->insertGetId([
            'name' => $name,
            'role' => $role,
            'email' => $email,
            'email_verified_at' => $verifiedAt,
            'password' => Hash::make(self::PASSWORD),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachUsersToOrganization(int $organizationId, array $userIds, $now): void
    {
        $rows = [];

        foreach ($userIds as $userId) {
            $rows[] = [
                'organization_id' => $organizationId,
                'user_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('organization_user')->insert($rows);
    }

    private function buildPermissionRows(
        int $organizationId,
        int $userId,
        array $permissionIds,
        int $grantedBy,
        $now
    ): array {
        $rows = [];

        foreach ($permissionIds as $permissionId) {
            $rows[] = [
                'organization_id' => $organizationId,
                'user_id' => $userId,
                'permission_id' => $permissionId,
                'granted_by' => $grantedBy,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $rows;
    }

    private function createDocument(
        int $applicationId,
        string $filename,
        string $filepath,
        string $mimetype,
        $now
    ): int {
        return DB::table('documents')->insertGetId([
            'application_id' => $applicationId,
            'filename' => $filename,
            'filepath' => $filepath,
            'mimetype' => $mimetype,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function insertApplicationAnswers(int $applicationId, array $answersByFieldId, $now): void
    {
        $rows = [];

        foreach ($answersByFieldId as $fieldId => $answer) {
            if (!is_array($answer)) {
                $answer = [
                    'value' => $answer,
                    'document_id' => null,
                ];
            }

            $rows[] = [
                'application_id' => $applicationId,
                'template_field_id' => $fieldId,
                'value' => $answer['value'] ?? null,
                'document_id' => $answer['document_id'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('application_answers')->insert($rows);
    }
}