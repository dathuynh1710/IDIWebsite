const IR_LIBRARY_URL = 'https://www.idiseafood.com/vn/quan-he-co-dong.html'
const BOND_LIBRARY_URL = 'https://www.idiseafood.com/vn/trai-phieu.html'

export const investorOverview = {
  ticker: 'IDI',
  exchange: 'HOSE',
  lastUpdated: '18/05/2026',
  contact: 'info@idiseafood.com',
  company: 'Công ty Cổ phần Đầu tư và Phát triển Đa Quốc Gia I.D.I',
  message: 'Minh bạch thông tin, đồng hành dài hạn cùng cổ đông.',
}

export const announcements = [
  { id: 'tb-2026-01', title: 'Hợp đồng kiểm toán năm 2026', date: '18/05/2026', year: '2026', type: 'Thông báo', href: IR_LIBRARY_URL },
  { id: 'tb-2026-02', title: 'Điều lệ công ty năm 2026', date: '08/05/2026', year: '2026', type: 'Quản trị', href: IR_LIBRARY_URL },
  { id: 'tb-2026-03', title: 'Ngày đăng ký cuối cùng và ngày giao dịch không hưởng quyền', date: '11/03/2026', year: '2026', type: 'Cổ đông', href: IR_LIBRARY_URL },
  { id: 'tb-2026-04', title: 'Xác nhận ngày đăng ký cuối cùng của VSDC', date: '04/03/2026', year: '2026', type: 'Cổ đông', href: IR_LIBRARY_URL },
  { id: 'tb-2026-05', title: 'Nghị quyết triệu tập và thông báo ngày chốt danh sách tham dự ĐHĐCĐ năm 2026', date: '02/03/2026', year: '2026', type: 'ĐHĐCĐ', href: IR_LIBRARY_URL },
  { id: 'tb-2026-06', title: 'Báo cáo tình hình quản trị công ty năm 2025', date: '28/01/2026', year: '2026', type: 'Quản trị', href: IR_LIBRARY_URL },
  { id: 'tb-2026-07', title: 'Nghị quyết giao dịch các bên liên quan năm 2026', date: '05/01/2026', year: '2026', type: 'Nghị quyết', href: IR_LIBRARY_URL },
  { id: 'tb-2025-01', title: 'Thông báo mẫu dấu mới của công ty', date: '29/09/2025', year: '2025', type: 'Thông báo', href: IR_LIBRARY_URL },
]

export const financialReports = [
  { id: 'bctc-2026-q1-hn', title: 'Báo cáo tài chính hợp nhất Quý 1 năm 2026', date: '28/04/2026', year: '2026', type: 'Hợp nhất', period: 'Quý 1', href: IR_LIBRARY_URL },
  { id: 'bctc-2026-q1-rieng', title: 'Báo cáo tài chính riêng Quý 1 năm 2026', date: '28/04/2026', year: '2026', type: 'Riêng', period: 'Quý 1', href: IR_LIBRARY_URL },
  { id: 'bctc-2026-q1-gt', title: 'Giải trình BCTC hợp nhất và riêng Quý 1 năm 2026', date: '28/04/2026', year: '2026', type: 'Giải trình', period: 'Quý 1', href: IR_LIBRARY_URL },
  { id: 'bctc-2025-y-hn', title: 'Báo cáo tài chính hợp nhất năm 2025 đã kiểm toán', date: '28/03/2026', year: '2025', type: 'Hợp nhất', period: 'Cả năm', href: IR_LIBRARY_URL },
  { id: 'bctc-2025-y-rieng', title: 'Báo cáo tài chính riêng năm 2025 đã kiểm toán', date: '28/03/2026', year: '2025', type: 'Riêng', period: 'Cả năm', href: IR_LIBRARY_URL },
  { id: 'bctc-2025-q4-hn', title: 'Báo cáo tài chính hợp nhất Quý 4 năm 2025', date: '29/01/2026', year: '2025', type: 'Hợp nhất', period: 'Quý 4', href: IR_LIBRARY_URL },
  { id: 'bctc-2025-q4-rieng', title: 'Báo cáo tài chính riêng Quý 4 năm 2025', date: '29/01/2026', year: '2025', type: 'Riêng', period: 'Quý 4', href: IR_LIBRARY_URL },
  { id: 'bctc-2025-q3-hn', title: 'Báo cáo tài chính hợp nhất Quý 3 năm 2025', date: '29/10/2025', year: '2025', type: 'Hợp nhất', period: 'Quý 3', href: IR_LIBRARY_URL },
  { id: 'bctc-2025-q3-rieng', title: 'Báo cáo tài chính riêng Quý 3 năm 2025', date: '29/10/2025', year: '2025', type: 'Riêng', period: 'Quý 3', href: IR_LIBRARY_URL },
  { id: 'bctc-2025-q3-gt', title: 'Giải trình BCTC riêng và hợp nhất Quý 3 năm 2025', date: '29/10/2025', year: '2025', type: 'Giải trình', period: 'Quý 3', href: IR_LIBRARY_URL },
]

export const annualReports = [
  { id: 'bctn-2025', title: 'Báo cáo thường niên năm 2025', date: '15/04/2026', year: '2025', type: 'Báo cáo thường niên', pages: '180 trang', href: 'https://idiseafood.com/vnt_upload/service/04_2026/BCTN_NAM_2025.pdf' },
  { id: 'bctn-2024', title: 'Báo cáo thường niên năm 2024', date: '15/04/2025', year: '2024', type: 'Báo cáo thường niên', href: IR_LIBRARY_URL },
  { id: 'bctn-2023', title: 'Báo cáo thường niên năm 2023', date: '15/04/2024', year: '2023', type: 'Báo cáo thường niên', href: IR_LIBRARY_URL },
  { id: 'bctn-2022', title: 'Báo cáo thường niên năm 2022', date: '14/04/2023', year: '2022', type: 'Báo cáo thường niên', href: IR_LIBRARY_URL },
  { id: 'bctn-2021', title: 'Báo cáo thường niên năm 2021', date: '15/04/2022', year: '2021', type: 'Báo cáo thường niên', href: IR_LIBRARY_URL },
]

export const agmDocuments = [
  { id: 'agm-2026-minutes', title: 'Biên bản họp và Nghị quyết ĐHĐCĐ thường niên năm 2026', date: '25/04/2026', year: '2026', type: 'Kết quả đại hội', href: IR_LIBRARY_URL },
  { id: 'agm-2026-proxy', title: 'Giấy ủy quyền tham dự ĐHĐCĐ thường niên năm 2026', date: '01/04/2026', year: '2026', type: 'Biểu mẫu', href: IR_LIBRARY_URL },
  { id: 'agm-2026-invitation', title: 'Thư mời tham dự ĐHĐCĐ thường niên năm 2026', date: '01/04/2026', year: '2026', type: 'Thư mời', href: IR_LIBRARY_URL },
  { id: 'agm-2026-docs', title: 'Tài liệu ĐHĐCĐ thường niên năm 2026', date: '01/04/2026', year: '2026', type: 'Tài liệu họp', href: IR_LIBRARY_URL },
  { id: 'agm-2025-minutes', title: 'Biên bản họp và Nghị quyết ĐHĐCĐ thường niên năm 2025', date: '24/04/2025', year: '2025', type: 'Kết quả đại hội', href: IR_LIBRARY_URL },
  { id: 'agm-2025-invitation', title: 'Thư mời tham dự ĐHĐCĐ thường niên năm 2025', date: '18/03/2025', year: '2025', type: 'Thư mời', href: 'https://idiseafood.com/vnt_upload/service/03_2025/3_Thu_moi_DHDCD_TN_2025_song_ngu.pdf' },
]

export const bondDocuments = [
  { id: 'bond-2025-audited', title: 'Tình hình sử dụng vốn trái phiếu đã kiểm toán năm 2025', date: '30/03/2026', year: '2025', type: 'Sử dụng vốn', href: BOND_LIBRARY_URL },
  { id: 'bond-2025-commitment', title: 'Tình hình thực hiện cam kết với người sở hữu trái phiếu năm 2025', date: '30/03/2026', year: '2025', type: 'Cam kết', href: BOND_LIBRARY_URL },
  { id: 'bond-2025-proceeds', title: 'Tình hình sử dụng nguồn vốn từ việc phát hành trái phiếu năm 2025', date: '30/03/2026', year: '2025', type: 'Sử dụng vốn', href: BOND_LIBRARY_URL },
  { id: 'bond-2025-interest', title: 'Tình hình thanh toán lãi trái phiếu năm 2025', date: '30/03/2026', year: '2025', type: 'Thanh toán lãi', href: BOND_LIBRARY_URL },
  { id: 'bond-2025-finance', title: 'Tình hình tài chính năm 2025', date: '30/03/2026', year: '2025', type: 'Tài chính', href: BOND_LIBRARY_URL },
  { id: 'bond-interest-3', title: 'Thông báo ngày đăng ký cuối cùng thực hiện quyền chi trả lãi trái phiếu kỳ 3', date: '28/03/2026', year: '2026', type: 'Thanh toán lãi', href: BOND_LIBRARY_URL },
  { id: 'bond-interest-2', title: 'Thông báo ngày đăng ký cuối cùng thực hiện quyền chi trả lãi trái phiếu kỳ 2', date: '01/10/2025', year: '2025', type: 'Thanh toán lãi', href: BOND_LIBRARY_URL },
  { id: 'bond-2024-audited', title: 'Báo cáo sử dụng vốn trái phiếu đã kiểm toán', date: '02/04/2025', year: '2024', type: 'Sử dụng vốn', href: 'https://www.idiseafood.com/vnt_upload/service/04_2025/bao_cao_su_dung_von_TP_da_kiem_toan_0001.pdf' },
  { id: 'bond-listing', title: 'Thông báo ngày giao dịch đầu tiên của trái phiếu', date: '05/12/2024', year: '2024', type: 'Niêm yết', href: BOND_LIBRARY_URL },
]

export const investorSources = {
  library: IR_LIBRARY_URL,
  bonds: BOND_LIBRARY_URL,
}

export const investorPageData = {
  overview: {
    title: 'Thông tin dành cho nhà đầu tư',
    description: 'Cập nhật minh bạch các công bố, nghị quyết và thông tin quản trị quan trọng của IDI.',
    stats: [
      { label: 'Mã chứng khoán', value: 'IDI', note: 'Niêm yết tại HOSE' },
      { label: 'Công bố mới nhất', value: '18/05/2026', note: 'Hợp đồng kiểm toán năm 2026' },
      { label: 'Nhóm tài liệu', value: '05', note: 'Được phân loại rõ ràng' },
      { label: 'Đầu mối IR', value: 'Email', note: 'info@idiseafood.com' },
    ],
    principles: [
      { title: 'Minh bạch', body: 'Thông tin được tổ chức theo thời gian, loại tài liệu và kỳ báo cáo để cổ đông dễ dàng tra cứu.' },
      { title: 'Kịp thời', body: 'Các công bố quan trọng được cập nhật theo lịch quản trị và nghĩa vụ công bố thông tin.' },
      { title: 'Đồng hành', body: 'IDI duy trì đầu mối quan hệ cổ đông để tiếp nhận và phản hồi các yêu cầu chính đáng.' },
    ],
  },
  financials: {
    title: 'Báo cáo tài chính',
    description: 'Hệ thống báo cáo riêng, hợp nhất và giải trình theo quý, bán niên và năm của IDI.',
    stats: [
      { label: 'Kỳ mới nhất', value: 'Quý 1/2026', note: 'Công bố ngày 28/04/2026' },
      { label: 'Báo cáo mới', value: '03', note: 'Riêng, hợp nhất và giải trình' },
      { label: 'BCTC năm 2025', value: 'Đã kiểm toán', note: 'Công bố ngày 28/03/2026' },
      { label: 'Chu kỳ công bố', value: 'Theo quý', note: 'Kèm báo cáo bán niên và năm' },
    ],
    guide: [
      { title: 'Báo cáo hợp nhất', body: 'Phản ánh tình hình tài chính và kết quả hoạt động của IDI cùng các đơn vị trong phạm vi hợp nhất.' },
      { title: 'Báo cáo riêng', body: 'Cung cấp số liệu của riêng công ty mẹ, thuận tiện cho việc đối chiếu và phân tích.' },
      { title: 'Văn bản giải trình', body: 'Làm rõ các biến động đáng chú ý theo quy định công bố thông tin.' },
    ],
  },
  annualReports: {
    title: 'Báo cáo thường niên',
    description: 'Bức tranh toàn diện về chiến lược, quản trị, hoạt động kinh doanh và định hướng phát triển của IDI qua từng năm.',
    stats: [
      { label: 'Báo cáo mới nhất', value: 'Năm 2025', note: 'Công bố ngày 15/04/2026' },
      { label: 'Kho lưu trữ', value: '05 năm', note: 'Từ năm 2021 đến 2025' },
      { label: 'Nội dung', value: 'Toàn diện', note: 'Chiến lược, quản trị và vận hành' },
      { label: 'Định dạng', value: 'PDF', note: 'Đọc hoặc tải về trực tuyến' },
    ],
    latest: {
      year: '2025',
      label: 'Báo cáo mới nhất',
      title: 'Kiến tạo chuỗi giá trị bền vững',
      body: 'Báo cáo thường niên 2025 tổng hợp dấu ấn hoạt động, năng lực quản trị và định hướng tăng trưởng dài hạn của IDI.',
      href: 'https://idiseafood.com/vnt_upload/service/04_2026/BCTN_NAM_2025.pdf',
      chapters: ['Tổng quan doanh nghiệp', 'Tình hình hoạt động', 'Quản trị công ty', 'Báo cáo tài chính'],
    },
  },
  agm: {
    title: 'Đại hội đồng cổ đông',
    description: 'Tài liệu, biểu mẫu và kết quả Đại hội đồng cổ đông thường niên của IDI được tập hợp theo từng năm.',
    stats: [
      { label: 'Kỳ đại hội', value: 'Năm 2026', note: 'Đại hội thường niên' },
      { label: 'Kết quả đại hội', value: '25/04/2026', note: 'Biên bản và nghị quyết' },
      { label: 'Hồ sơ', value: '04 nhóm', note: 'Mời họp, tài liệu, biểu mẫu, kết quả' },
      { label: 'Hình thức', value: 'Công khai', note: 'Tra cứu trực tuyến' },
    ],
    process: [
      { step: '01', title: 'Thông báo & mời họp', body: 'Cung cấp thời gian, địa điểm và điều kiện tham dự đại hội.' },
      { step: '02', title: 'Tài liệu & biểu mẫu', body: 'Tập hợp chương trình, tờ trình, dự thảo nghị quyết và giấy ủy quyền.' },
      { step: '03', title: 'Kết quả đại hội', body: 'Công bố biên bản, nghị quyết và các tài liệu kèm theo sau đại hội.' },
    ],
  },
  bond: {
    title: 'Trái phiếu xanh IDI',
    description: 'Thông tin phát hành, thanh toán và báo cáo sử dụng vốn của lô trái phiếu xanh thủy sản tiên phong.',
    stats: [
      { label: 'Mã trái phiếu', value: 'IDIH2432001', note: 'Phát hành trong nước' },
      { label: 'Giá trị phát hành', value: '1.000 tỷ đồng', note: '1.000 trái phiếu' },
      { label: 'Kỳ hạn', value: '08 năm', note: 'Đáo hạn 31/10/2032' },
      { label: 'Lãi suất cố định', value: '5,58%/năm', note: 'Bảo lãnh bởi GuarantCo' },
    ],
    impact: [
      { title: 'Chuỗi giống chất lượng', body: 'Nguồn vốn hỗ trợ phát triển cơ sở giống, củng cố nền tảng vùng nuôi bền vững.' },
      { title: 'Nâng cao năng lực chế biến', body: 'Đầu tư nhà máy và công nghệ để gia tăng hiệu quả sử dụng nguyên liệu.' },
      { title: 'Tích hợp chuỗi giá trị', body: 'Kết nối vùng nuôi, chế biến và thị trường theo định hướng tăng trưởng xanh.' },
    ],
    award: {
      label: 'Sustainable Debt Awards 2025',
      title: 'Trái phiếu xanh của năm – Khối doanh nghiệp APAC',
      body: 'Lô trái phiếu được ghi nhận nhờ tính tiên phong trong lĩnh vực nuôi trồng thủy sản và cấu trúc bảo lãnh thanh toán 100% từ GuarantCo.',
    },
  },
}
