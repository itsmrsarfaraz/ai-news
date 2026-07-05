<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Category $category */
        $category = $this->route('category');

        return (bool) $this->user()?->can('update', $category);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Category $category */
        $category = $this->route('category');

        return [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $parentId = $this->input('parent_id');

            if ($parentId === null) {
                return;
            }

            /** @var Category $category */
            $category = $this->route('category');

            if ((int) $parentId === $category->id) {
                $validator->errors()->add('parent_id', 'A category cannot be its own parent.');

                return;
            }

            if ($this->isDescendant($category, (int) $parentId)) {
                $validator->errors()->add('parent_id', 'A category cannot be moved under one of its own descendants.');
            }
        });
    }

    /**
     * Walk down from $category to see whether $candidateId appears among
     * its descendants, to prevent circular parent/child chains.
     */
    private function isDescendant(Category $category, int $candidateId): bool
    {
        foreach ($category->children()->pluck('id') as $childId) {
            if ($childId === $candidateId) {
                return true;
            }

            $child = Category::query()->find($childId);

            if ($child !== null && $this->isDescendant($child, $candidateId)) {
                return true;
            }
        }

        return false;
    }
}
