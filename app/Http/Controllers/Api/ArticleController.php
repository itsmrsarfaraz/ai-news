<?php

namespace App\Http\Controllers\Api;

use App\Actions\GenerateUniqueSlugAction;
use App\Enums\Language;
use App\Enums\PublishStatus;
use App\Enums\WorkflowStage;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class ArticleController extends Controller
{
    private const EAGER_LOAD = ['category', 'author', 'editor', 'featuredMedia'];

    public function __construct(
        private readonly Gate $gate,
        private readonly GenerateUniqueSlugAction $slugger,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->gate->authorize('viewAny', Article::class);

        $perPage = (int) $request->integer('per_page', 15);
        $perPage = min(max($perPage, 1), 50);

        $query = Article::query()
            ->with(self::EAGER_LOAD)
            ->when(
                $request->user() === null,
                fn ($query) => $query->published(),
                fn ($query) => $query->when(
                    $request->filled('status'),
                    fn ($query) => $query->where('status', $request->string('status'))
                )
            )
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when($request->filled('language'), fn ($query) => $query->where('language', $request->string('language')))
            ->when($request->boolean('breaking'), fn ($query) => $query->breaking())
            ->when($request->boolean('featured'), fn ($query) => $query->featured())
            ->when($request->filled('category'), function ($query) use ($request) {
                $categoryId = is_numeric($request->input('category'))
                    ? (int) $request->input('category')
                    : Category::query()->where('slug', $request->string('category'))->value('id');

                if ($categoryId !== null) {
                    $query->inCategory($categoryId);
                }
            })
            ->latest('published_at');

        // Guests browsing the public site get cheap cursor pagination
        // (no COUNT(*) query, ideal for infinite-scroll news listings).
        // Authenticated staff get numbered pagination for admin tables.
        $articles = $request->user() === null
            ? $query->cursorPaginate($perPage)
            : $query->paginate($perPage);

        return ArticleResource::collection($articles);
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $additionalCategoryIds = $data['additional_category_ids'] ?? [];
        unset($data['additional_category_ids']);

        $data['slug'] = $data['slug'] ?? $this->slugger->handle($data['title'], 'articles');
        $data['author_id'] = $data['author_id'] ?? $request->user()?->id;
        $data['status'] = $data['status'] ?? PublishStatus::Draft->value;
        $data['workflow_stage'] = $data['workflow_stage'] ?? WorkflowStage::Research->value;
        $data['language'] = $data['language'] ?? Language::Urdu->value;

        if ($data['status'] === PublishStatus::Published->value && empty($data['published_at'])) {
            $data['published_at'] = Carbon::now();
        }

        $article = Article::query()->create($data);

        if ($additionalCategoryIds !== []) {
            $article->additionalCategories()->sync($additionalCategoryIds);
        }

        $article->load(self::EAGER_LOAD);

        return ArticleResource::make($article)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Article $article): ArticleResource
    {
        $this->gate->authorize('view', $article);

        $article->loadMissing([...self::EAGER_LOAD, 'additionalCategories', 'media']);

        return ArticleResource::make($article);
    }

    public function update(UpdateArticleRequest $request, Article $article): ArticleResource
    {
        $data = $request->validated();
        $additionalCategoryIds = $data['additional_category_ids'] ?? null;
        unset($data['additional_category_ids']);

        if (array_key_exists('title', $data) && empty($data['slug'] ?? null)) {
            $data['slug'] = $this->slugger->handle($data['title'], 'articles', $article->id);
        }

        $newStatus = $data['status'] ?? $article->status->value;

        if ($newStatus === PublishStatus::Published->value
            && $article->status !== PublishStatus::Published
            && empty($data['published_at'] ?? $article->published_at)) {
            $data['published_at'] = Carbon::now();
        }

        $article->update($data);

        if ($additionalCategoryIds !== null) {
            $article->additionalCategories()->sync($additionalCategoryIds);
        }

        $article->load(self::EAGER_LOAD);

        return ArticleResource::make($article);
    }

    public function destroy(Article $article): JsonResponse
    {
        $this->gate->authorize('delete', $article);

        $article->delete();

        return response()->json(status: 204);
    }
}
