<?php

namespace Tests\Feature;

use App\Livewire\Admin\Recruitment\ApplicationIndex;
use App\Livewire\Admin\Recruitment\PositionForm;
use App\Models\JobApplication;
use App\Models\JobPosition;
use App\Models\User;
use Database\Seeders\BusinessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RecruitmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruitment_manager_can_open_all_module_screens(): void
    {
        $user = $this->manager();
        $position = $this->position();

        foreach ([
            '/admin/recruitment', '/admin/recruitment/settings',
            '/admin/recruitment/create', "/admin/recruitment/{$position->id}/edit",
            '/admin/recruitment/applications',
        ] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }

        $this->actingAs($user)->get("/admin/recruitment/{$position->id}/preview?locale=en")
            ->assertOk()->assertSee('Export Sales Executive');
    }

    public function test_position_is_saved_in_three_languages_and_published_to_api(): void
    {
        $user = $this->manager();

        Livewire::actingAs($user)->test(PositionForm::class)
            ->set('code', 'REC-2026-001')
            ->set('department', 'Kinh doanh')
            ->set('quantity', 3)
            ->set('title', ['vi' => 'Nhân viên kinh doanh', 'en' => 'Sales Executive', 'zh' => '销售专员'])
            ->set('slug', ['vi' => 'nhan-vien-kinh-doanh', 'en' => 'sales-executive', 'zh' => 'xiaoshou-zhuanyuan'])
            ->set('location', ['vi' => 'Hồ Chí Minh', 'en' => 'Ho Chi Minh City', 'zh' => '胡志明市'])
            ->set('description.vi', '<p>Tư vấn khách hàng</p><script>alert(1)</script>')
            ->set('translation_status', ['vi' => 'published', 'en' => 'published', 'zh' => 'published'])
            ->call('save')
            ->assertHasNoErrors();

        $position = JobPosition::where('code', 'REC-2026-001')->firstOrFail();
        $this->assertSame('销售专员', $position->getTranslation('title', 'zh'));
        $this->assertStringNotContainsString('<script', $position->getTranslation('description', 'vi'));
        $this->assertDatabaseHas('localized_routes', [
            'routeable_type' => JobPosition::class, 'routeable_id' => $position->id,
            'locale' => 'en', 'full_path' => '/en/careers/sales-executive',
        ]);

        $this->getJson('/api/careers?locale=en')
            ->assertOk()->assertJsonPath('items.0.title', 'Sales Executive');
    }

    public function test_public_application_upload_and_admin_review_flow(): void
    {
        Storage::fake('public');
        $user = $this->manager();
        $position = $this->position();

        $response = $this->post('/api/careers/applications', [
            'jobPositionId' => $position->id,
            'fullName' => 'Nguyễn Minh Anh',
            'email' => 'minhanh@example.com',
            'phone' => '0901234567',
            'address' => 'Đồng Tháp',
            'coverLetter' => 'Tôi mong muốn gia nhập IDI.',
            'cv' => UploadedFile::fake()->create('cv-minh-anh.pdf', 120, 'application/pdf'),
        ]);

        $response->assertCreated()->assertJsonStructure(['referenceId']);
        $application = JobApplication::firstOrFail();

        Livewire::actingAs($user)->test(ApplicationIndex::class)
            ->call('viewApplication', $application->id)
            ->set('detailStatus', 'shortlisted')
            ->set('internalNote', 'Mời phỏng vấn vòng một.')
            ->call('saveReview')
            ->assertHasNoErrors();

        $this->assertSame('shortlisted', $application->fresh()->status->value);
        $this->assertSame('Mời phỏng vấn vòng một.', $application->fresh()->internal_note);
    }

    public function test_user_without_recruitment_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/recruitment')->assertForbidden();
    }

    public function test_database_seeder_creates_multilingual_recruitment_samples_without_duplicates(): void
    {
        $this->seed();

        $this->assertSame(4, JobPosition::whereIn('code', [
            'SALES_EXPORT_01', 'QA_SUPERVISOR_01', 'IT_SYSTEM_01', 'HR_RECRUITMENT_01',
        ])->count());
        $this->assertSame(6, JobApplication::whereIn('email', [
            'minhtri.sales@example.com', 'ngocanh.export@example.com',
            'thanhhuong.qa@example.com', 'trungkien.it@example.com',
            'thaithien.it@example.com', 'vandang.hr@example.com',
        ])->count());

        $quality = JobPosition::where('code', 'QA_SUPERVISOR_01')->firstOrFail();
        $this->assertSame('Quality assurance supervisor', $quality->getTranslation('title', 'en'));
        $this->assertSame('质量保证主管', $quality->getTranslation('title', 'zh'));
        $this->assertDatabaseHas('localized_routes', [
            'routeable_type' => JobPosition::class,
            'routeable_id' => $quality->id,
            'locale' => 'zh',
            'full_path' => '/zh/zhaopin/zhiliang-baozheng-zhuguan',
        ]);

        $this->seed(BusinessSeeder::class);
        $this->assertSame(4, JobPosition::whereIn('code', [
            'SALES_EXPORT_01', 'QA_SUPERVISOR_01', 'IT_SYSTEM_01', 'HR_RECRUITMENT_01',
        ])->count());
        $this->assertSame(6, JobApplication::whereIn('email', [
            'minhtri.sales@example.com', 'ngocanh.export@example.com',
            'thanhhuong.qa@example.com', 'trungkien.it@example.com',
            'thaithien.it@example.com', 'vandang.hr@example.com',
        ])->count());
    }

    private function manager(): User
    {
        foreach ([
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt'],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English'],
            ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文'],
        ] as $index => $locale) {
            DB::table('locales')->updateOrInsert(['code' => $locale['code']], $locale + [
                'direction' => 'ltr', 'is_default' => $index === 0, 'is_active' => true,
                'sort_order' => $index, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('recruitment.manage', 'web'));
        return $user;
    }

    private function position(): JobPosition
    {
        return JobPosition::create([
            'code' => 'REC-SALES',
            'title' => ['vi' => 'Nhân viên kinh doanh xuất khẩu', 'en' => 'Export Sales Executive', 'zh' => '出口销售专员'],
            'slug' => ['vi' => 'nhan-vien-kinh-doanh-xuat-khau', 'en' => 'export-sales-executive', 'zh' => 'chukou-xiaoshou'],
            'location' => ['vi' => 'Hồ Chí Minh', 'en' => 'Ho Chi Minh City', 'zh' => '胡志明市'],
            'translation_status' => ['vi' => 'published', 'en' => 'published', 'zh' => 'published'],
            'quantity' => 2,
            'is_active' => true,
        ]);
    }
}
