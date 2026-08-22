import InvestorDocumentsPage from '@components/investors/InvestorDocumentsPage'

export default function AnnouncementsPage() {
  return (
    <InvestorDocumentsPage
      category="thong-bao"
      title="Thông báo"
      description="Thông báo, nghị quyết và các nội dung quản trị được IDI công bố đến cổ đông."
      libraryTitle="Thông báo dành cho cổ đông"
      libraryDescription="Tra cứu thông báo theo ngày công bố, năm hoặc số hiệu văn bản."
    />
  )
}
