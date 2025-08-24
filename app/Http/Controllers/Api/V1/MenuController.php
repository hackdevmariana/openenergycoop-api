<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Menu\StoreMenuRequest;
use App\Http\Requests\Api\V1\Menu\UpdateMenuRequest;
use App\Http\Resources\Api\V1\MenuResource;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Menus",
 *     description="Sistema de navegación y menús del sitio web"
 * )
 */
class MenuController extends \App\Http\Controllers\Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Menu::query()
            ->with(['parent', 'organization'])
            ->published()
            ->active()
            ->ordered();

        if ($request->filled('menu_group')) {
            $query->byGroup($request->menu_group);
        }

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null') {
                $query->rootItems();
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        if ($request->boolean('include_children')) {
            $query->with(['children' => function($query) {
                $query->published()->active()->ordered();
            }]);
        }

        $menus = $query->get();
        return MenuResource::collection($menus);
    }

    public function store(StoreMenuRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by_user_id'] = auth()->id();

        $menu = Menu::create($validated);
        $menu->load(['parent', 'children', 'organization']);

        return response()->json([
            'data' => new MenuResource($menu),
            'message' => 'Elemento de menú creado exitosamente'
        ], 201);
    }

    public function show(Request $request, Menu $menu): JsonResponse
    {
        if (!$menu->isPublished() || !$menu->is_active) {
            return response()->json(['message' => 'Elemento de menú no encontrado'], 404);
        }

        $relations = ['organization'];
        
        if ($request->boolean('include_hierarchy')) {
            $relations[] = 'parent';
            $relations[] = 'children';
        }

        $menu->load($relations);

        return response()->json([
            'data' => new MenuResource($menu)
        ]);
    }

    public function update(UpdateMenuRequest $request, Menu $menu): JsonResponse
    {
        $validated = $request->validated();
        $validated['updated_by_user_id'] = auth()->id();

        $menu->update($validated);
        $menu->load(['parent', 'children', 'organization']);

        return response()->json([
            'data' => new MenuResource($menu),
            'message' => 'Elemento de menú actualizado exitosamente'
        ]);
    }

    public function destroy(Menu $menu): JsonResponse
    {
        if ($menu->hasChildren()) {
            return response()->json([
                'message' => 'No se puede eliminar un elemento de menú que tiene elementos hijos'
            ], 422);
        }

        $menu->delete();

        return response()->json([
            'message' => 'Elemento de menú eliminado exitosamente'
        ]);
    }

    public function byGroup(Request $request, string $group): JsonResponse
    {
        if (!in_array($group, array_keys(Menu::MENU_GROUPS))) {
            return response()->json(['message' => 'Grupo de menú no válido'], 422);
        }

        $query = Menu::query()
            ->with(['children.children.children'])
            ->published()
            ->active()
            ->byGroup($group)
            ->rootItems()
            ->ordered();

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        $menus = $query->get();

        return response()->json([
            'data' => MenuResource::collection($menus),
            'group' => $group,
            'group_label' => Menu::MENU_GROUPS[$group],
            'total_items' => $menus->count()
        ]);
    }

    public function hierarchy(Request $request): JsonResponse
    {
        $query = Menu::query()
            ->with(['children.children'])
            ->published()
            ->active()
            ->rootItems()
            ->ordered();

        if ($request->filled('language')) {
            $query->inLanguage($request->language);
        }

        $menus = $query->get();
        $grouped = $menus->groupBy('menu_group');

        $result = [];
        foreach (Menu::MENU_GROUPS as $key => $label) {
            $result[$key] = [
                'label' => $label,
                'items' => MenuResource::collection($grouped->get($key, collect())),
                'count' => $grouped->get($key, collect())->count()
            ];
        }

        return response()->json([
            'data' => $result,
            'total_groups' => count(Menu::MENU_GROUPS),
            'total_items' => $menus->count()
        ]);
    }
}