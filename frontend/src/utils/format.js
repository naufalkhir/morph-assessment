// formatCurrency — formats numbers as currency strings
// Uses browser's built-in Intl API — no external library needed
// Example: formatCurrency(1234.5, 'MYR') → 'RM 1,234.50'
//          formatCurrency(320, 'USD')     → 'US$320.00'
export function formatCurrency(amount, currency = 'MYR') {
  return new Intl.NumberFormat('en-MY', {
    style: 'currency',    // formats as currency with symbol
    currency,             // dynamic — uses whatever currency is passed in
    minimumFractionDigits: 2, // always show 2 decimal places
  }).format(amount)
}

// formatDate — converts date string to readable format
// Example: formatDate('2026-03-10') → '10 Mar 2026'
export function formatDate(dateStr) {
  // Guard clause — return empty string if no date provided
  if (!dateStr) return ''

  const d = new Date(dateStr)

  // Guard clause — return original string if date is invalid
  if (isNaN(d)) return dateStr

  // en-GB locale gives DD MMM YYYY format — e.g. '10 Mar 2026'
  return d.toLocaleDateString('en-GB', {
    day: '2-digit',   // 10
    month: 'short',   // Mar
    year: 'numeric',  // 2026
  })
}