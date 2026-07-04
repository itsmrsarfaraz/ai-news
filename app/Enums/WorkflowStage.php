<?php

namespace App\Enums;

/**
 * Tracks where an article sits in the editorial/AI pipeline:
 * Research -> Fact Collection -> Outline -> Draft Generation ->
 * Urdu Refinement -> SEO Optimization -> Media Assignment ->
 * Quality Assurance -> Editorial Review -> Approval -> Scheduling ->
 * Publishing -> Monitoring.
 *
 * This is independent of `PublishStatus`: an article can be
 * `PublishStatus::Draft` while its `workflow_stage` is anywhere
 * from Research through Approval.
 */
enum WorkflowStage: string
{
    case Research = 'research';
    case FactCollection = 'fact_collection';
    case Outline = 'outline';
    case DraftGeneration = 'draft_generation';
    case UrduRefinement = 'urdu_refinement';
    case SeoOptimization = 'seo_optimization';
    case MediaAssignment = 'media_assignment';
    case QualityAssurance = 'quality_assurance';
    case EditorialReview = 'editorial_review';
    case Approval = 'approval';
    case Scheduling = 'scheduling';
    case Publishing = 'publishing';
    case Monitoring = 'monitoring';

    public function label(): string
    {
        return match ($this) {
            self::Research => 'Research',
            self::FactCollection => 'Fact Collection',
            self::Outline => 'Outline',
            self::DraftGeneration => 'Draft Generation',
            self::UrduRefinement => 'Urdu Language Refinement',
            self::SeoOptimization => 'SEO Optimization',
            self::MediaAssignment => 'Media Assignment',
            self::QualityAssurance => 'Quality Assurance',
            self::EditorialReview => 'Editorial Review',
            self::Approval => 'Approval',
            self::Scheduling => 'Scheduling',
            self::Publishing => 'Publishing',
            self::Monitoring => 'Monitoring',
        };
    }

    /**
     * The stage that follows this one, or null if this is terminal.
     */
    public function next(): ?self
    {
        $order = self::cases();
        $index = array_search($this, $order, strict: true);

        return $order[$index + 1] ?? null;
    }
}
