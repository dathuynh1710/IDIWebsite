import { useState } from 'react'
import InvestorDocumentLibrary from './InvestorDocumentLibrary'
import InvestorPageHeader from './InvestorPageHeader'

export default function InvestorDocumentsPage({
  category = '',
  title,
  description,
  libraryTitle = 'Thư viện tài liệu',
}) {
  const [pageConfig, setPageConfig] = useState(null)
  const isOverview = category === ''

  return (
    <>
      <InvestorPageHeader
        title={isOverview && pageConfig?.title ? pageConfig.title : title}
        description={isOverview && pageConfig?.description ? pageConfig.description : description}
        seo={isOverview ? pageConfig?.seo : null}
        uppercaseTitle={isOverview}
        structuredDescription={isOverview}
      />
      <InvestorDocumentLibrary
        category={category}
        title={libraryTitle}
        onPageConfigChange={isOverview ? setPageConfig : undefined}
      />
    </>
  )
}
