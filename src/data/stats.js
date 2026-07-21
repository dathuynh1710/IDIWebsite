/**
 * stats.js — Key company statistics for homepage hero bar and About page.
 * Update these values when figures change — they render on multiple pages.
 */
export const COMPANY_STATS = [
  {
    id: 'countries',
    value: 50,
    suffix: '+',
    label: 'Countries Served',
    description: 'Exporting to 50+ markets across 5 continents',
  },
  {
    id: 'capacity',
    value: 100000,
    suffix: ' MT',
    label: 'Annual Capacity',
    description: 'Processing capacity in metric tonnes per year',
  },
  {
    id: 'founded',
    value: 1997,
    suffix: '',
    label: 'Year Founded',
    description: 'Nearly 30 years of pangasius expertise',
  },
  {
    id: 'staff',
    value: 5000,
    suffix: '+',
    label: 'Employees',
    description: 'Skilled workforce across all operations',
  },
]

export const SUSTAINABILITY_STATS = [
  { id: 'co2',        value: 28,   suffix: '%', label: 'CO₂ Reduced',           description: 'Year-over-year emissions reduction' },
  { id: 'farms',      value: 100,  suffix: '%', label: 'Sustainable Farms',      description: 'ASC-certified aquaculture operations' },
  { id: 'community',  value: 5000, suffix: '+', label: 'Communities Supported',  description: 'People benefiting from social programs' },
]
