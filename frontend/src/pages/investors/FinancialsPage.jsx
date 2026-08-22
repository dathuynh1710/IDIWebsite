import InvestorDocumentsPage from '@components/investors/InvestorDocumentsPage'

export default function FinancialsPage() {
  return (
    <InvestorDocumentsPage
      category="bao-cao-tai-chinh"
      title="Báo cáo tài chính"
      description="Báo cáo riêng, hợp nhất và văn bản giải trình theo từng kỳ công bố của IDI."
      libraryTitle="Thư viện báo cáo tài chính"
      libraryDescription="Tra cứu theo năm, quý, số hiệu hoặc tên báo cáo."
    />
  )
}
