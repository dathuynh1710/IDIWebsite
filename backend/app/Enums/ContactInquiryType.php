<?php

namespace App\Enums;

enum ContactInquiryType: string
{
    case ExportQuote = 'export_quote';
    case ProductAdvice = 'product_advice';
    case BusinessCooperation = 'business_cooperation';
    case InvestorRelations = 'investor_relations';
    case Careers = 'careers';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::ExportQuote => 'Báo giá xuất khẩu',
            self::ProductAdvice => 'Tư vấn sản phẩm',
            self::BusinessCooperation => 'Hợp tác kinh doanh',
            self::InvestorRelations => 'Quan hệ nhà đầu tư',
            self::Careers => 'Tuyển dụng',
            self::Other => 'Yêu cầu khác',
        };
    }

    public static function normalize(string $value): ?self
    {
        $value = trim($value);

        if ($type = self::tryFrom($value)) {
            return $type;
        }

        foreach (self::cases() as $type) {
            if ($type->label() === $value) {
                return $type;
            }
        }

        return null;
    }
}
