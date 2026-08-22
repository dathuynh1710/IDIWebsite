import { useState } from 'react'
import InvestorDocumentLibrary from './InvestorDocumentLibrary'
import InvestorPageHeader from './InvestorPageHeader'

export default function InvestorDocumentsPage({
  category = '',
  title,
  description,
  eyebrow = 'Quan hệ cổ đông',
  libraryTitle = 'Thư viện tài liệu',
  libraryDescription = 'Tra cứu các công bố và tài liệu dành cho cổ đông.',
}) {
  const [pageConfig, setPageConfig] = useState(null)
  const isOverview = category === ''

  return (
    <>
      <InvestorPageHeader
        title={isOverview && pageConfig?.title ? pageConfig.title : title}
        description={isOverview && pageConfig?.description ? pageConfig.description : description}
        eyebrow={eyebrow}
        seo={isOverview ? pageConfig?.seo : null}
      />
      <InvestorDocumentLibrary
        category={category}
        title={libraryTitle}
        description={libraryDescription}
        onPageConfigChange={isOverview ? setPageConfig : undefined}
      />
    </>
  )
}
