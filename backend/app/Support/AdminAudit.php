<?php

namespace App\Support;

use App\Models\ContactMessage;
use App\Models\DocumentCategory;
use App\Models\InvestorDocument;
use App\Models\JobApplication;
use App\Models\JobPosition;
use App\Models\OfficeLocation;
use App\Models\Page;
use App\Models\Permission;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Recipe;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdminAudit
{
    /** @var array<class-string<Model>, string> */
    public const MODULES = [
        User::class => 'Quản trị viên',
        Role::class => 'Vai trò',
        Permission::class => 'Quyền hạn',
        Product::class => 'Sản phẩm',
        ProductCategory::class => 'Danh mục sản phẩm',
        Post::class => 'Tin tức',
        PostCategory::class => 'Danh mục tin tức',
        Page::class => 'Giới thiệu',
        Recipe::class => 'Recipes',
        DocumentCategory::class => 'Quan hệ cổ đông',
        InvestorDocument::class => 'Quan hệ cổ đông',
        JobPosition::class => 'Tuyển dụng',
        JobApplication::class => 'Hồ sơ ứng tuyển',
        ContactMessage::class => 'Liên hệ',
        OfficeLocation::class => 'Địa điểm liên hệ',
    ];

    public static function log(string $action, string $module, string $description, ?Model $subject = null, array $properties = []): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $request = request();
        $logger = activity('admin')
            ->causedBy($user)
            ->event($action)
            ->withProperties(array_merge([
                'module' => $module,
                'action' => $action,
                'actor_name' => $user->name,
                'actor_username' => $user->username,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
            ], $properties));

        if ($subject) {
            $logger->performedOn($subject);
        }

        $logger->log($description);
    }

    public static function logModelEvent(Model $model, string $action): void
    {
        if (! auth()->check()) {
            return;
        }

        $module = self::MODULES[$model::class] ?? class_basename($model);
        $label = self::subjectLabel($model);
        $verbs = ['created' => 'Tạo', 'updated' => 'Cập nhật', 'deleted' => 'Xóa'];
        $properties = ['subject_label' => $label];

        if ($action === 'updated') {
            $properties['fields_changed'] = collect(array_keys($model->getChanges()))
                ->reject(fn (string $field) => in_array($field, ['updated_at', 'password', 'remember_token'], true))
                ->values()->all();
        }

        self::log($action, $module, ($verbs[$action] ?? ucfirst($action)).' '.$module.' “'.$label.'”', $model, $properties);
    }

    public static function subjectLabel(Model $model): string
    {
        foreach (['name', 'title', 'subject', 'code', 'sku', 'email', 'username'] as $field) {
            $value = $model->getAttribute($field);
            if (is_array($value)) {
                $value = $value['vi'] ?? reset($value);
            }
            if (is_string($value) && trim($value) !== '') {
                return Str::limit(strip_tags($value), 80);
            }
        }

        return '#'.$model->getKey();
    }
}
