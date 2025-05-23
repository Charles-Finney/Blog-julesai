import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      // Proxying requests from /blog/backend/api to http://localhost/blog/backend/api
      // The key '/blog/backend/api' means any request starting with this path will be proxied.
      '/blog/backend/api': {
        target: 'http://localhost', // Assuming PHP server runs on http://localhost (port 80)
        changeOrigin: true, // Needed for virtual hosted sites
        secure: false, // Set to true if your backend is HTTPS, but for localhost dev, false is common
        // Optional: You can rewrite the path if needed, but for this setup, it's not necessary
        // as the target path structure matches the request path structure.
        // rewrite: (path) => path.replace(/^\/api_proxy/, '') 
      }
    }
  }
})
