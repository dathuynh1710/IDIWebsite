<?php

namespace Tests\Feature;

use App\Enums\ContactStatus;
use App\Livewire\Admin\Contacts\Index;
use App\Livewire\Admin\Contacts\Settings;
use App\Models\ContactMessage;
use App\Models\OfficeLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ContactManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_view_and_filter_contact_messages(): void
    {
        $user = $this->contactManager();
        $this->message(['full_name' => 'Nguyễn Thùy Trang', 'subject' => 'Tư vấn mua sản phẩm']);
        $this->message(['full_name' => 'Safet Erdogan', 'subject' => 'Custodian partnership', 'status' => ContactStatus::Resolved]);

        $this->actingAs($user)
            ->get('/admin/contacts')
            ->assertOk()
            ->assertSee('Quản lý liên lạc')
            ->assertSee('Nguyễn Thùy Trang')
            ->assertSee('Safet Erdogan');

        Livewire::actingAs($user)->test(Index::class)
            ->set('search', 'Thùy Trang')
            ->assertSee('Nguyễn Thùy Trang')
            ->assertDontSee('Safet Erdogan')
            ->set('search', '')
            ->set('status', 'resolved')
            ->assertSee('Safet Erdogan')
            ->assertDontSee('Nguyễn Thùy Trang');
    }

    public function test_opening_message_marks_it_in_progress_and_assigns_user(): void
    {
        $user = $this->contactManager();
        $message = $this->message();

        Livewire::actingAs($user)->test(Index::class)
            ->call('viewMessage', $message->id)
            ->assertSet('viewingMessageId', $message->id)
            ->assertSet('detailStatus', ContactStatus::InProgress->value);

        $message->refresh();
        $this->assertSame(ContactStatus::InProgress, $message->status);
        $this->assertSame($user->id, $message->assigned_to);
    }

    public function test_status_and_bulk_actions_are_persisted(): void
    {
        $user = $this->contactManager();
        $first = $this->message();
        $second = $this->message(['email' => 'second@example.com']);

        Livewire::actingAs($user)->test(Index::class)
            ->call('viewMessage', $first->id)
            ->set('detailStatus', ContactStatus::Resolved->value)
            ->call('updateDetailStatus')
            ->assertHasNoErrors();
        $this->assertSame(ContactStatus::Resolved, $first->fresh()->status);
        $this->assertNotNull($first->fresh()->replied_at);

        Livewire::actingAs($user)->test(Index::class)
            ->set('selected', [$first->id, $second->id])
            ->call('bulk', 'unread')
            ->assertHasNoErrors();
        $this->assertSame(ContactStatus::New, $first->fresh()->status);
        $this->assertSame(ContactStatus::New, $second->fresh()->status);
    }

    public function test_user_without_contact_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/contacts')
            ->assertForbidden();
    }

    public function test_contact_settings_and_office_locations_support_three_languages(): void
    {
        $user = $this->contactManager();

        $this->actingAs($user)->get('/admin/contacts/settings')
            ->assertOk()->assertSee('Cấu hình liên lạc')->assertSee('English')->assertSee('中文');

        Livewire::actingAs($user)->test(Settings::class)
            ->set('page_title', ['vi' => 'Liên hệ', 'en' => 'Contact us', 'zh' => '联系我们'])
            ->set('description.vi', '<p>Thông tin liên hệ</p><script>alert(1)</script>')
            ->set('success_message.en', 'Thank you for contacting us.')
            ->set('notification_email', 'info@idiseafood.com')
            ->set('items_per_page', 20)
            ->call('saveSettings')
            ->assertHasNoErrors()
            ->set('location_code', 'HCMC_OFFICE')
            ->set('location_name', ['vi' => 'Văn phòng Hồ Chí Minh', 'en' => 'Ho Chi Minh City office', 'zh' => '胡志明市办事处'])
            ->set('location_address', ['vi' => 'Quận 1, TP.HCM', 'en' => 'District 1, HCMC', 'zh' => '胡志明市第一郡'])
            ->set('location_email', 'hcm@idiseafood.com')
            ->set('location_sort_order', 1)
            ->call('saveLocation')
            ->assertHasNoErrors();

        $module = DB::table('modules')->where('code', 'contact')->first();
        $this->assertSame('Contact us', json_decode($module->page_title, true)['en']);
        $this->assertStringNotContainsString('<script', json_decode($module->description, true)['vi']);
        $office = OfficeLocation::where('code', 'HCMC_OFFICE')->firstOrFail();
        $this->assertSame('胡志明市办事处', $office->getTranslation('name', 'zh'));
    }

    public function test_contact_list_can_filter_by_language(): void
    {
        $user = $this->contactManager();
        $this->message(['full_name' => 'Khách Việt', 'email' => 'vi@example.com', 'locale' => 'vi']);
        $this->message(['full_name' => 'English Customer', 'email' => 'en@example.com', 'locale' => 'en']);

        Livewire::actingAs($user)->test(Index::class)
            ->set('locale', 'en')
            ->assertSee('English Customer')
            ->assertDontSee('Khách Việt');
    }

    private function contactManager(): User
    {
        foreach ([
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'sort_order' => 0, 'is_default' => true],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'sort_order' => 1, 'is_default' => false],
            ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文', 'sort_order' => 2, 'is_default' => false],
        ] as $locale) {
            DB::table('locales')->updateOrInsert(['code' => $locale['code']], $locale + [
                'direction' => 'ltr', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $permission = Permission::findOrCreate('contacts.manage', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        return $user;
    }

    private function message(array $attributes = []): ContactMessage
    {
        return ContactMessage::create(array_merge([
            'full_name' => 'Khách hàng IDI',
            'email' => 'customer@example.com',
            'phone' => '0901234567',
            'subject' => 'Yêu cầu báo giá',
            'message' => 'Tôi muốn nhận thông tin chi tiết về sản phẩm cá tra phi lê.',
            'locale' => null,
            'status' => ContactStatus::New,
        ], $attributes));
    }
}
