<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

/**
 * NOTE: There is no roles/permissions table yet (tracked as technical debt
 * from Milestone 2). Until a Role/Permission system exists, "any
 * authenticated user" is treated as editorial staff and may edit
 * unassigned (e.g. AI-generated) drafts. Once roles land, replace the
 * blanket authenticated-user checks with role checks (Editor/Admin can
 * touch anything; Writer only their own articles).
 */
class ArticlePolicy
{
    /**
     * Anyone can request the article listing; the controller itself
     * restricts *which* articles (published-only for guests).
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Published articles are public. Unpublished/scheduled/draft
     * articles are only visible to authenticated staff.
     */
    public function view(?User $user, Article $article): bool
    {
        if ($article->status === \App\Enums\PublishStatus::Published) {
            return true;
        }

        return $user !== null;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * The assigned author or editor may update the article. Unassigned
     * articles (e.g. a fresh AI-generated draft with no author_id yet)
     * may be claimed/edited by any authenticated staff member.
     */
    public function update(User $user, Article $article): bool
    {
        if ($article->author_id === null && $article->editor_id === null) {
            return true;
        }

        return $user->id === $article->author_id || $user->id === $article->editor_id;
    }

    public function delete(User $user, Article $article): bool
    {
        return $this->update($user, $article);
    }
}
