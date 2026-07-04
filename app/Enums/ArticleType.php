<?php

namespace App\Enums;

/**
 * The content type of an article.
 *
 * This is the same value that will be passed as `content_type` to the
 * generic AI Worker Engine (ResearchWorker, OutlineWorker, SEOOptimizer,
 * PublisherWorker, etc.) so that worker logic stays reusable while
 * prompts, validation rules, templates, and required `meta` fields vary
 * per type.
 */
enum ArticleType: string
{
    case StandardNews = 'standard_news';
    case BreakingNews = 'breaking_news';
    case LiveBlog = 'live_blog';
    case FeatureStory = 'feature_story';
    case Editorial = 'editorial';
    case Opinion = 'opinion';
    case Interview = 'interview';
    case InvestigativeReport = 'investigative_report';
    case JobListing = 'job_listing';
    case ScholarshipListing = 'scholarship_listing';
    case AdmissionAnnouncement = 'admission_announcement';
    case VisaGuide = 'visa_guide';
    case GovernmentNotification = 'government_notification';
    case PressRelease = 'press_release';
    case EventListing = 'event_listing';
    case ProductReview = 'product_review';
    case Tutorial = 'tutorial';
    case EvergreenGuide = 'evergreen_guide';
    case Faq = 'faq';
    case PhotoGallery = 'photo_gallery';
    case VideoArticle = 'video_article';
    case Infographic = 'infographic';
    case ExternalRoundup = 'external_roundup';

    /**
     * Human-readable label for admin UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::StandardNews => 'Standard News Article',
            self::BreakingNews => 'Breaking News',
            self::LiveBlog => 'Live Blog',
            self::FeatureStory => 'Feature Story',
            self::Editorial => 'Editorial',
            self::Opinion => 'Opinion Piece',
            self::Interview => 'Interview',
            self::InvestigativeReport => 'Investigative Report',
            self::JobListing => 'Job Listing',
            self::ScholarshipListing => 'Scholarship Listing',
            self::AdmissionAnnouncement => 'Admission Announcement',
            self::VisaGuide => 'Visa & Immigration Guide',
            self::GovernmentNotification => 'Government Notification',
            self::PressRelease => 'Press Release',
            self::EventListing => 'Event Listing',
            self::ProductReview => 'Product Review',
            self::Tutorial => 'Tutorial',
            self::EvergreenGuide => 'Evergreen Guide',
            self::Faq => 'FAQ',
            self::PhotoGallery => 'Photo Gallery',
            self::VideoArticle => 'Video Article',
            self::Infographic => 'Infographic',
            self::ExternalRoundup => 'External Source Roundup',
        };
    }

    /**
     * The `meta` JSON keys expected for this content type. Used by
     * FormRequests and AI workers to know what to validate/generate.
     *
     * @return array<int, string>
     */
    public function expectedMetaKeys(): array
    {
        return match ($this) {
            self::JobListing => ['department', 'eligibility', 'deadline', 'application_method', 'official_links'],
            self::ScholarshipListing => ['eligibility', 'funding', 'required_documents', 'application_process', 'deadline', 'benefits', 'official_website'],
            self::AdmissionAnnouncement => ['programs', 'merit_criteria', 'fee_structure', 'deadline', 'admission_process', 'contact_information'],
            self::VisaGuide => ['country', 'requirements', 'processing_time', 'fees', 'documentation', 'eligibility', 'official_references'],
            self::EventListing => ['event_date', 'venue', 'organizer', 'registration_link'],
            default => [],
        };
    }
}
