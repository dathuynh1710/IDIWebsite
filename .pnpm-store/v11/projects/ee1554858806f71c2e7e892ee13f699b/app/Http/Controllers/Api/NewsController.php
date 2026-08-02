<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $locale = $this->locale($request);
        $limit = min(100, max(1, $request->integer('limit', 12)));
        $posts = Post::query()
            ->with(['category', 'featuredMedia', 'author'])
            ->where('is_active', true)
            ->where("translation_status->{$locale}", 'published')
            ->when($request->filled('category'), function ($query) use ($request, $locale): void {
                $category = $request->string('category')->toString();
                $query->whereHas('category', fn ($q) => $q
                    ->where('id', $category)
                    ->orWhere("name->{$locale}", $category)
                    ->orWhere("slug->{$locale}", $category));
            })
            ->orderByDesc('is_featured')->orderByDesc('sort_order')->latest('created_at')
            ->paginate($limit);

        return response()->json([
            'items' => collect($posts->items())->map(fn (Post $post) => $this->article($post, $locale)),
            'total' => $posts->total(), 'page' => $posts->currentPage(), 'limit' => $posts->perPage(),
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $locale = $this->locale($request);
        $post = Post::with(['category', 'featuredMedia', 'author'])
            ->where('is_active', true)->where("slug->{$locale}", $slug)->firstOrFail();

        return response()->json(['data' => $this->article($post, $locale)]);
    }

    private function locale(Request $request): string
    {
        $locale = $request->string('locale', $request->string('lang', 'vi')->toString())->toString();
        return in_array($locale, ['vi', 'en', 'zh'], true) ? $locale : 'vi';
    }

    private function article(Post $post, string $locale): array
    {
        return [
            'id' => $post->id,
            'slug' => $post->getTranslation('slug', $locale, false),
            'title' => $post->getTranslation('title', $locale, false),
            'excerpt' => $post->getTranslation('excerpt', $locale, false),
            'content' => $post->getTranslation('content', $locale, false),
            'category' => [
                'id' => $post->category?->id,
                'name' => $post->category?->getTranslation('name', $locale, false),
                'slug' => $post->category?->getTranslation('slug', $locale, false),
            ],
            'image' => $post->featuredMedia ? [
                'url' => $post->featuredMedia->url,
                'alt' => $post->getTranslation('title', $locale, false),
            ] : null,
            'author' => ['name' => $post->author?->name ?: 'Ban Truyền thông IDI'],
            'date' => $post->getTranslation('locale_published_at', $locale, false) ?: $post->created_at?->toIso8601String(),
            'updatedAt' => $post->updated_at?->toIso8601String(),
            'isFeatured' => $post->is_featured,
            'seo' => [
                'title' => $post->getTranslation('seo_title', $locale, false),
                'description' => $post->getTranslation('meta_description', $locale, false),
            ],
        ];
    }
}
