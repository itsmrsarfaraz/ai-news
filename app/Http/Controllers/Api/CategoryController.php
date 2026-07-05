<?php

namespace App\Http\Controllers\Api;

use App\Actions\GenerateUniqueSlugAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function __construct(
        private readonly Gate $gate,
        private readonly GenerateUniqueSlugAction $slugger,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->gate->authorize('viewAny', Category::class);

        $perPage = (int) $request->integer('per_page', 25);
        $perPage = min(max($perPage, 1), 100);

        $categories = Category::query()
            ->withCount('articles')
            ->when($request->has('parent_id'), function ($query) use ($request) {
                $parentId = $request->input('parent_id');
                $parentId === 'null' || $parentId === null
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', (int) $parentId);
            })
            ->when($request->boolean('active_only'), fn ($query) => $query->active())
            ->ordered()
            ->paginate($perPage);

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['slug'] = $data['slug'] ?? $this->slugger->handle($data['name'], 'categories');

        $category = Category::query()->create($data);

        return CategoryResource::make($category)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Category $category): CategoryResource
    {
        $this->gate->authorize('view', $category);

        $category->loadMissing(['parent', 'children'])->loadCount('articles');

        return CategoryResource::make($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $data = $request->validated();

        if (array_key_exists('name', $data) && empty($data['slug'] ?? null)) {
            $data['slug'] = $this->slugger->handle($data['name'], 'categories', $category->id);
        }

        $category->update($data);

        return CategoryResource::make($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->gate->authorize('delete', $category);

        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a category that still has subcategories. Reassign or delete them first.',
            ], 409);
        }

        if ($category->articles()->exists() || $category->taggedArticles()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a category that still has articles assigned to it. Reassign those articles first.',
            ], 409);
        }

        $category->delete();

        return response()->json(status: 204);
    }
}
