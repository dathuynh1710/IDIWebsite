import { RouterProvider } from 'react-router'
import { router } from './router'
import Providers from './providers'

/**
 * App — Root component.
 * Composes: Providers (global state) → RouterProvider (navigation).
 * Keep this file minimal — all logic lives in router.jsx and providers.jsx.
 */
export default function App() {
  return (
    <Providers>
      <RouterProvider router={router} />
    </Providers>
  )
}
