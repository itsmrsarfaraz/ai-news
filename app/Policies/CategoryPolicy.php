<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/**
 * NOTE: There is no roles/permissions table yet (tracked as technical debt
 * from Milestone 2). Until a Role/Permission system exists, "any
 * authenticated user" is treated as editorial staff. Once roles land,
 * tighten create/update/delete to specific roles (e.g. Editor/Admin).
 */
class CategoryPolicy
{
    /**
     * Anyone can browse the category list (public site navigation).
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Active categories are publicly visible. Inactive categories are
     * only visible to authenticated staff previewing the admin panel.
     */
    public function view(?User $user, Category $category): bool
    {
        return $category->is_active || $user !== null;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Category $category): bool
    {
        return true;
    }

    public function delete(User $user, Category $category): bool
    {
        return true;
    }
}
