<?php

namespace Tests\Feature;

use App\Enums\ContactStatus;
use App\Livewire\Admin\Contacts\Index;
use App\Livewire\Admin\Contacts\Settings;
use App\Models\ContactMessage;
use App\Models\OfficeLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ContactManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_contact_form_creates_a_message_and_returns_a_reference(): void
    {
        $this->createLocale('vi');

        $response = $this->postJson('/api/contacts', [
            'inquiryType' => 'Báo giá xuất khẩu',
            'fullName' => 'Nguyễn Minh Anh',
            'phone' => '+84 901 234 567',
            'email' => 'minhanh@example.com',
            'address' => 'Đồng Tháp, Việt Nam',
            'subject' => 'Yêu cầu báo giá',
            'message' => 'Vui lòng gửi thông tin báo giá sản phẩm cá tra phi lê.',
            'consent' => true,
            'companyWebsite' => '',
            'locale' => 'vi',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('toast.type', 'success')
            ->assertJsonPath('toast.message', 'Gửi liên hệ thành công.')
            ->assertJsonStructure(['message', 'referenceId']);

        $this->assertDatabaseHas('contact_messages', [
            'inquiry_type' => 'Báo giá xuất khẩu',
            'full_name' => 'Nguyễn Minh Anh',
            'email' => 'minhanh@example.com',
            'address' => 'Đồng Tháp, Việt Nam',
            'locale' => 'vi',
            'status' => ContactStatus::Unread->value,
        ]);
        $this->assertNotNull(ContactMessage::firstOrFail()->consented_at);
    }

    public function test_public_contact_form_validates_fields_and_discards_honeypot_submissions(): void
    {
        $this->postJson('/api/contacts', [
            'inquiryType' => 'Yêu cầu khác',
            'fullName' => 'A',
            'phone' => 'invalid',
            'email' => 'invalid',
            'address' => '',
            'subject' => 'No',
            'message' => 'Short',
            'consent' => false,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'fullName', 'phone', 'email', 'address', 'subject', 'message', 'consent',
            ]);

        $this->postJson('/api/contacts', [
            'inquiryType' => 'Yêu cầu khác',
            'fullName' => 'Website Robot',
            'phone' => '0901234567',
            'email' => 'robot@example.com',
            'address' => 'Internet',
            'subject' => 'Promotional offer',
            'message' => 'This is an automated promotional submission.',
            'consent' => true,
            'companyWebsite' => 'https://spam.example.com',
        ])->assertCreated()->assertJsonPath('success', true);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_authorized_user_can_view_and_filter_contact_messages(): void
    {
        $user = $this->contactManager();
        $this->message(['full_name' => 'Nguyễn Thùy Trang', 'subject' => 'Tư vấn mua sản phẩm']);
        $this->message(['full_name' => 'Safet Erdogan', 'subject' => 'Custodian partnership', 'status' => ContactStatus::Read]);

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
            ->set('status', ContactStatus::Read->value)
            ->assertSee('Safet Erdogan')
            ->assertDontSee('Nguyễn Thùy Trang');
    }

    public function test_contact_pagination_uses_livewire_actions_without_a_duplicated_admin_path(): void
    {
        $user = $this->contactManager();

        foreach (range(1, 16) as $index) {
            $this->message([
                'full_name' => "Khách hàng {$index}",
                'email' => "customer{$index}@example.com",
            ]);
        }

        $component = Livewire::actingAs($user)->test(Index::class);

        $this->assertStringContainsString('wire:click="gotoPage(2, \'page\')"', $component->html());
        $this->assertStringNotContainsString('/admin/admin/contacts', $component->html());
        $this->assertStringContainsString('wire:model.live="perPage"', $component->html());

        $component->call('gotoPage', 2, 'page')
            ->assertSet('paginators.page', 2);
        $this->assertStringContainsString('<strong>6–10</strong>', $component->html());

        $component->set('perPage', 10)
            ->assertSet('perPage', 10)
            ->assertSet('paginators.page', 1);
    }

    public function test_opening_message_marks_it_as_read_and_assigns_user(): void
    {
        $user = $this->contactManager();
        $message = $this->message();

        Livewire::actingAs($user)->test(Index::class)
            ->call('viewMessage', $message->id)
            ->assertSet('viewingMessageId', $message->id);

        $message->refresh();
        $this->assertSame(ContactStatus::Read, $message->status);
        $this->assertSame($user->id, $message->assigned_to);
    }

    public function test_read_status_cannot_be_reverted_and_bulk_delete_is_persisted(): void
    {
        $user = $this->contactManager();
        $first = $this->message();
        $second = $this->message(['email' => 'second@example.com']);

        Livewire::actingAs($user)->test(Index::class)
            ->call('viewMessage', $first->id)
            ->set('detailStatus', ContactStatus::Unread->value)
            ->call('closeMessage')
            ->assertSet('viewingMessageId', null)
            ->assertSet('detailStatus', '')
            ->call('viewMessage', $first->id);
        $this->assertSame(ContactStatus::Read, $first->fresh()->status);

        Livewire::actingAs($user)->test(Index::class)
            ->set('selected', [$second->id])
            ->call('requestBulkDelete')
            ->assertSet('pendingBulkDelete', true)
            ->assertSee('Xóa thư liên hệ?')
            ->call('confirmDelete')
            ->assertHasNoErrors();
        $this->assertSame(ContactStatus::Read, $first->fresh()->status);
        $this->assertDatabaseMissing('contact_messages', ['id' => $second->id]);
    }

    public function test_single_delete_requires_custom_modal_confirmation(): void
    {
        $user = $this->contactManager();
        $message = $this->message(['full_name' => 'Người gửi cần xác nhận']);

        Livewire::actingAs($user)->test(Index::class)
            ->call('requestDelete', $message->id)
            ->assertSet('pendingDeleteId', $message->id)
            ->assertSet('pendingDeleteName', 'Người gửi cần xác nhận')
            ->assertSee('Không, giữ lại')
            ->call('cancelDelete')
            ->assertSet('pendingDeleteId', null);

        $this->assertDatabaseHas('contact_messages', ['id' => $message->id]);

        Livewire::actingAs($user)->test(Index::class)
            ->call('requestDelete', $message->id)
            ->call('confirmDelete');

        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
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
            ->assertOk()
            ->assertSee('Cấu hình liên lạc')
            ->assertSee('English')
            ->assertSee('中文')
            ->assertDontSee('Cấu hình chung');

        Livewire::actingAs($user)->test(Settings::class)
            ->set('page_title', ['vi' => 'Liên hệ', 'en' => 'Contact us', 'zh' => '联系我们'])
            ->set('description.vi', '<p style="text-align:center"><span style="color:#0f6ab4">Thông tin liên hệ</span></p><table><tbody><tr><td>Văn phòng</td></tr></tbody></table><script>alert(1)</script>')
            ->set('success_message.en', 'Thank you for contacting us.')
            ->set('notification_email', 'info@idiseafood.com')
            ->set('items_per_page', 20)
            ->call('saveSettings')
            ->assertHasNoErrors()
            ->set('location_code', 'HCMC_OFFICE')
            ->set('location_name', ['vi' => 'Văn phòng Hồ Chí Minh', 'en' => 'Ho Chi Minh City office', 'zh' => '胡志明市办事处'])
            ->set('location_company', ['vi' => 'Công ty Cổ phần Đầu tư và Phát triển Đa Quốc Gia I.D.I', 'en' => 'I.D.I International Development and Investment Corporation', 'zh' => 'I.D.I 国际发展投资股份公司'])
            ->set('location_address', ['vi' => 'Quận 1, TP.HCM', 'en' => 'District 1, HCMC', 'zh' => '胡志明市第一郡'])
            ->set('location_phone', '+84 277 376 8899')
            ->set('location_fax', '+84 277 368 0382')
            ->set('location_email', 'hcm@idiseafood.com')
            ->set('location_map_type', 'none')
            ->set('location_sort_order', 1)
            ->call('saveLocation')
            ->assertHasNoErrors();

        $module = DB::table('modules')->where('code', 'contact')->first();
        $this->assertSame('Contact us', json_decode($module->page_title, true)['en']);
        $this->assertStringNotContainsString('<script', json_decode($module->description, true)['vi']);
        $this->assertStringContainsString('<span style="color:#0f6ab4">', json_decode($module->description, true)['vi']);
        $this->assertStringContainsString('<table>', json_decode($module->description, true)['vi']);
        $office = OfficeLocation::where('code', 'HCMC_OFFICE')->firstOrFail();
        $this->assertSame('胡志明市办事处', $office->getTranslation('name', 'zh'));
        $this->assertSame('I.D.I International Development and Investment Corporation', $office->getTranslation('company', 'en'));
        $this->assertSame('+84 277 368 0382', $office->fax);
    }

    public function test_office_location_supports_all_four_map_options(): void
    {
        Storage::fake('public');
        $user = $this->contactManager();

        $component = Livewire::actingAs($user)->test(Settings::class)
            ->call('createLocation')
            ->assertSee('Google Maps Embed')
            ->assertSee('Google Maps')
            ->assertSee('Ảnh bản đồ')
            ->assertSee('Không hiển thị')
            ->set('location_code', 'MAP_TEST')
            ->set('location_name.vi', 'Văn phòng bản đồ')
            ->set('location_address.vi', 'Đồng Tháp, Việt Nam')
            ->set('location_map_type', 'google_maps')
            ->set('location_map_url', 'https://maps.app.goo.gl/AbCdEf123456')
            ->call('saveLocation')
            ->assertHasNoErrors();

        $office = OfficeLocation::where('code', 'MAP_TEST')->firstOrFail();
        $this->assertSame('google_maps', $office->map_type);
        $this->assertSame('https://maps.app.goo.gl/AbCdEf123456', $office->map_url);

        $component->call('editLocation', $office->id)
            ->set('location_map_type', 'embed')
            ->set('location_map_embed', '<iframe src="https://www.google.com/maps/embed?pb=test" width="600" height="450"></iframe>')
            ->call('saveLocation')
            ->assertHasNoErrors();
        $this->assertSame('embed', $office->fresh()->map_type);
        $this->assertStringContainsString('https://www.google.com/maps/embed?pb=test', $office->fresh()->map_embed);

        $component->call('editLocation', $office->id)
            ->set('location_map_type', 'image')
            ->set('location_map_image', UploadedFile::fake()->createWithContent(
                'office-map.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZQmcAAAAASUVORK5CYII=')
            ))
            ->call('saveLocation')
            ->assertHasNoErrors();
        $imagePath = $office->fresh()->map_image;
        $this->assertNotNull($imagePath);
        Storage::disk('public')->assertExists($imagePath);

        $component->call('editLocation', $office->id)
            ->set('location_map_type', 'none')
            ->call('saveLocation')
            ->assertHasNoErrors();
        $this->assertSame('none', $office->fresh()->map_type);
        $this->assertNull($office->fresh()->map_embed);
        $this->assertNull($office->fresh()->map_url);
        $this->assertNull($office->fresh()->map_image);
    }

    public function test_office_location_sort_order_can_be_updated_inline(): void
    {
        $user = $this->contactManager();
        $office = OfficeLocation::create([
            'code' => 'INLINE_ORDER',
            'name' => ['vi' => 'Văn phòng cập nhật thứ tự'],
            'address' => ['vi' => 'Đồng Tháp'],
            'map_type' => 'none',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Livewire::actingAs($user)->test(Settings::class)
            ->assertSee('updateLocationSortOrder')
            ->call('updateLocationSortOrder', $office->id, 12)
            ->assertHasNoErrors()
            ->call('updateLocationSortOrder', $office->id, -1)
            ->assertHasErrors(["location_sort_orders.{$office->id}"]);

        $this->assertSame(12, $office->fresh()->sort_order);
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

    private function createLocale(string $code): void
    {
        DB::table('locales')->insert([
            'code' => $code,
            'name' => 'Vietnamese',
            'native_name' => 'Tiếng Việt',
            'direction' => 'ltr',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
            'status' => ContactStatus::Unread,
        ], $attributes));
    }
}
