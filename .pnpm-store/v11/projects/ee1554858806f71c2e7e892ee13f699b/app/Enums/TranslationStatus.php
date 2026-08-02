<?php

namespace App\Enums;

enum TranslationStatus: string
{
    case Draft = 'draft';
    case Translating = 'translating';
    case Review = 'review';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Hidden = 'hidden';
    case Archived = 'archived';
}
