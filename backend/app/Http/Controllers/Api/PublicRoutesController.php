<?php

namespace App\Http\Controllers\Api;

use App\Support\PublicRoutes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicRoutesController
{
    public function index()
    {
        return response()->json(['entries' => PublicRoutes::entries(), 'redirects' => DB::table('redirects')->where('is_active', true)->pluck('to_path', 'from_path')]);
    }

    public function resolve(string $path, string $locale = 'vi'): ?string
    {
        $entries = PublicRoutes::entries();
        $valid = array_merge(...array_map(fn ($entry) => array_values($entry['paths']), $entries));
        if (in_array($path, $valid)) {
            return $path;
        }
        $target = $path;
        $seen = [];
        while (! isset($seen[$target])) {
            $seen[$target] = true;
            $next = DB::table('redirects')->where('is_active', true)->where('from_path', $target)->value('to_path');
            if (! $next) {
                break;
            }
            $target = $next;
            if (in_array($target, $valid)) {
                return $target;
            }
        }
        $about = ['/about' => 'ABOUT_MESSAGE', '/about/story' => 'ABOUT_HISTORY', '/about/values' => 'ABOUT_VALUES'];
        foreach ($entries as $entry) {
            if (($entry['legacy'] ?? null) === $path || (isset($about[$path]) && ($entry['code'] ?? null) === $about[$path])) {
                return $entry['paths'][$locale] ?? null;
            }
            foreach (($entry['slugs'] ?? []) as $slug) {
                if ($path === '/'.$entry['group'].'/'.$slug) {
                    return $entry['paths'][$locale] ?? null;
                }
            }
        }

        return null;
    }

    public function document(Request $request)
    {
        $path = '/'.trim($request->path(), '/');
        $requestedPath = $request->getPathInfo();
        $target = $this->resolve($path);
        if ($request->filled('category') && ($path === '/products' || in_array($path, PublicRoutes::statics()['/products']))) {
            foreach (PublicRoutes::entries() as $entry) {
                if ($entry['name'] === 'product-categories.show' && in_array($request->query('category'), $entry['slugs'])) {
                    $locale = preg_match('~^/(vi|en|zh)/~', $path, $match) ? $match[1] : 'vi';
                    $target = $entry['paths'][$locale] ?? $target;
                    break;
                }
            }
        }
        $query = $request->server('QUERY_STRING');
        if ($target && $target !== $requestedPath) {
            return redirect($target.($query ? '?'.$query : ''), 301);
        }
        abort_unless($target, 404);
        $index = config('app.frontend_index', base_path('../frontend/dist/index.html'));
        abort_unless(is_file($index), 503, 'Build frontend assets first.');

        return response()->file($index);
    }

    public function sitemap()
    {
        $origin = rtrim(config('app.frontend_url', config('app.url')), '/');
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
        $escape = fn ($value) => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $newsSetting = DB::table('module_settings')->join('modules', 'modules.id', '=', 'module_settings.module_id')
            ->where('modules.code', 'news')->where('setting_key', 'sitemap_enabled')->value('setting_value');
        $newsEnabled = $newsSetting === null || json_decode($newsSetting, true) !== false;
        foreach (PublicRoutes::entries() as $entry) {
            if (! $newsEnabled && (($entry['group'] ?? null) === 'news' || ($entry['legacy'] ?? null) === '/news')) {
                continue;
            }
            foreach (($entry['sitemapPaths'] ?? $entry['paths']) as $path) {
                $xml .= '<url><loc>'.$escape($origin.$path).'</loc>';
                foreach ($entry['paths'] as $locale => $alternate) {
                    $xml .= '<xhtml:link rel="alternate" hreflang="'.$locale.'" href="'.$escape($origin.$alternate).'"/>';
                }
                $xml .= '</url>';
            }
        }

        return response($xml.'</urlset>')->header('Content-Type', 'application/xml');
    }
}
