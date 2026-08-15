<?php

namespace Tests\Feature;

use App\Livewire\Admin\Recruitment\ApplicationIndex;
use App\Livewire\Admin\Recruitment\PositionForm;
use App\Livewire\Admin\Recruitment\PositionIndex;
use App\Livewire\Admin\Recruitment\Settings as RecruitmentSettings;
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
        JobApplication::create([
            'job_position_id' => $position->id,
            'full_name' => 'Ứng viên kiểm thử',
            'email' => 'pagination@example.com',
            'phone' => '0900000000',
            'address' => 'Đồng Tháp',
        ]);

        foreach ([
            '/admin/recruitment', '/admin/recruitment/settings',
            '/admin/recruitment/create', "/admin/recruitment/{$position->id}/edit",
            '/admin/recruitment/applications',
        ] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }

        $this->actingAs($user)->get('/admin/recruitment')->assertSee('Hiển thị')->assertSee('/ trang');
        $this->actingAs($user)->get('/admin/recruitment/applications')->assertSee('Hiển thị')->assertSee('/ trang');

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
            ->set('enabled_locales', ['vi', 'en', 'zh'])
            ->set('title', ['vi' => 'Nhân viên kinh doanh', 'en' => 'Sales Executive', 'zh' => '销售专员'])
            ->set('slug', ['vi' => 'nhan-vien-kinh-doanh', 'en' => 'sales-executive', 'zh' => 'xiaoshou-zhuanyuan'])
            ->set('location', ['vi' => 'Hồ Chí Minh', 'en' => 'Ho Chi Minh City', 'zh' => '胡志明市'])
            ->set('description.vi', '<p>Tư vấn khách hàng</p><script>alert(1)</script>')
            ->set('requirements.vi', '<ul><li>Giao tiếp tốt</li></ul>')
            ->set('benefits.vi', '<p>Thưởng doanh số</p>')
            ->set('contact.vi', '<p>Liên hệ Phòng Nhân sự</p>')
            ->set('meta_keywords.vi', 'tuyển dụng, kinh doanh, IDI Seafood')
            ->call('save')
            ->assertHasNoErrors();

        $position = JobPosition::where('code', 'REC-2026-001')->firstOrFail();
        $this->assertSame('销售专员', $position->getTranslation('title', 'zh'));
        $this->assertSame('<p>Liên hệ Phòng Nhân sự</p>', $position->getTranslation('contact', 'vi'));
        $this->assertSame('tuyển dụng, kinh doanh, IDI Seafood', $position->getTranslation('meta_keywords', 'vi'));
        $this->assertStringNotContainsString('<script', $position->getTranslation('description', 'vi'));
        $this->assertDatabaseHas('localized_routes', [
            'routeable_type' => JobPosition::class, 'routeable_id' => $position->id,
            'locale' => 'en', 'full_path' => '/en/careers/sales-executive',
        ]);

        $this->getJson('/api/careers?locale=en')
            ->assertOk()->assertJsonPath('items.0.title', 'Sales Executive');

        $this->getJson('/api/careers/sales-executive?locale=en')
            ->assertOk()
            ->assertJsonPath('data.contact', '<p>Liên hệ Phòng Nhân sự</p>')
            ->assertJsonPath('data.seo.keywords', 'tuyển dụng, kinh doanh, IDI Seafood');
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
            ->set('detailStatus', 'reviewing')
            ->set('internalNote', 'Mời phỏng vấn vòng một.')
            ->call('saveReview')
            ->assertHasNoErrors();

        $this->assertSame('reviewing', $application->fresh()->status->value);
        $this->assertSame('Mời phỏng vấn vòng một.', $application->fresh()->internal_note);

        Livewire::actingAs($user)->test(ApplicationIndex::class)
            ->set('selected', [$application->id])
            ->set("pendingStatuses.{$application->id}", 'new')
            ->call('updateSelected')
            ->assertHasNoErrors();

        $this->assertSame('new', $application->fresh()->status->value);
    }

    public function test_recruitment_deletes_use_custom_confirmation_modals(): void
    {
        $user = $this->manager();
        $position = $this->position();
        $application = JobApplication::create([
            'job_position_id' => $position->id,
            'full_name' => 'Ứng viên chờ xác nhận',
            'email' => 'confirm@example.com',
        ]);

        Livewire::actingAs($user)->test(PositionIndex::class)
            ->call('requestDelete', $position->id)
            ->assertSet('pendingDeleteId', $position->id)
            ->assertSee('Xóa vị trí tuyển dụng?')
            ->call('cancelDelete');
        $this->assertNotSoftDeleted($position);

        Livewire::actingAs($user)->test(ApplicationIndex::class)
            ->call('requestDelete', $application->id)
            ->assertSet('pendingDeleteId', $application->id)
            ->assertSee('Xóa hồ sơ ứng tuyển?')
            ->call('confirmDelete');
        $this->assertDatabaseMissing('job_applications', ['id' => $application->id]);
    }

    public function test_recruitment_settings_are_saved_and_published_to_the_public_api(): void
    {
        Storage::fake('public');
        $user = $this->manager();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);

        Livewire::actingAs($user)->test(RecruitmentSettings::class)
            ->set('page_title', ['vi' => 'Tuyển dụng IDI', 'en' => 'IDI Careers', 'zh' => 'IDI 招聘'])
            ->set('description.vi', '<p>Môi trường phát triển bền vững.</p><script>alert(1)</script>')
            ->set('benefits_content.vi', '<h2>Phúc lợi</h2><p>Bảo hiểm và đào tạo.</p>')
            ->set('contact_content.vi', '<p>Liên hệ Phòng Nhân sự.</p>')
            ->set('seo_title.vi', 'Cơ hội nghề nghiệp tại IDI')
            ->set('meta_description.vi', 'Thông tin tuyển dụng mới nhất tại IDI Seafood.')
            ->set('meta_keywords.vi', 'tuyển dụng, IDI Seafood')
            ->set('application_enabled', false)
            ->set('hero_desktop', UploadedFile::fake()->createWithContent('careers-desktop.png', $png))
            ->set('hero_mobile', UploadedFile::fake()->createWithContent('careers-mobile.png', $png))
            ->set('gallery_uploads', [UploadedFile::fake()->createWithContent('team.png', $png)])
            ->call('save')
            ->assertHasNoErrors();

        $moduleId = (int) DB::table('modules')->where('code', 'careers')->value('id');
        $settings = DB::table('module_settings')->where('module_id', $moduleId)
            ->pluck('setting_value', 'setting_key')->map(fn ($value) => json_decode($value, true));

        $this->assertFalse($settings['application_enabled']);
        $this->assertStringNotContainsString('<script', DB::table('modules')->where('id', $moduleId)->value('description'));
        Storage::disk('public')->assertExists($settings['hero_desktop']);
        Storage::disk('public')->assertExists($settings['hero_mobile']);
        Storage::disk('public')->assertExists($settings['gallery_images'][0]);

        $this->getJson('/api/careers?locale=vi')
            ->assertOk()
            ->assertJsonPath('limit', 10)
            ->assertJsonPath('pageConfig.title', 'Tuyển dụng IDI')
            ->assertJsonPath('pageConfig.applicationEnabled', false)
            ->assertJsonPath('pageConfig.metaKeywords', 'tuyển dụng, IDI Seafood')
            ->assertJsonCount(1, 'pageConfig.gallery');

        $this->postJson('/api/careers/applications', [])->assertForbidden();
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
        $this->assertSame(8, JobApplication::whereIn('email', [
            'thanhbinhdtcc@gmail.com', 'pthienkim849@gmail.com',
            'trungkien240398@gmail.com', 'daohuynhnhu2004@gmail.com',
            'nguyentran2008cb@gmail.com', 'thaithienca@gmail.com',
            'vodangdt@gmail.com', 'ltduong2102@gmail.com',
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
        $this->assertSame(8, JobApplication::whereIn('email', [
            'thanhbinhdtcc@gmail.com', 'pthienkim849@gmail.com',
            'trungkien240398@gmail.com', 'daohuynhnhu2004@gmail.com',
            'nguyentran2008cb@gmail.com', 'thaithienca@gmail.com',
            'vodangdt@gmail.com', 'ltduong2102@gmail.com',
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
