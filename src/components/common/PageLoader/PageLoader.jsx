/**
 * PageLoader — Full-screen loading state shown while lazy page chunks download.
 * Shown by the Suspense boundary in RootLayout.
 */
export default function PageLoader() {
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-arctic-white">
      <div className="flex flex-col items-center gap-4">
        {/* Animated fish wave logo placeholder */}
        <div className="relative w-16 h-16">
          <div className="absolute inset-0 rounded-full border-4 border-light-mist" />
          <div
            className="absolute inset-0 rounded-full border-4 border-ocean-deep border-t-transparent animate-spin"
            style={{ animationDuration: '0.8s' }}
          />
        </div>
        <div className="flex flex-col items-center gap-1">
          <p className="text-sm font-semibold text-ocean-deep tracking-widest uppercase">
            IDI Seafood
          </p>
          <p className="text-xs text-storm-grey">Loading...</p>
        </div>
      </div>
    </div>
  )
}
