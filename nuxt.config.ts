// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@nuxt/eslint',
    '@nuxt/ui',
    '@pinia/nuxt',
    '@vite-pwa/nuxt'
  ],

  imports: {
    dirs: [
      'composables/services'
    ]
  },

  devtools: {
    enabled: true
  },

  css: ['~/assets/css/main.css'],

  runtimeConfig: {
    public: {
      vapidPublicKey: ''
    }
  },

  routeRules: {
    '/': { prerender: true }
  },

  compatibilityDate: '2026-06-30',

  eslint: {
    config: {
      stylistic: {
        commaDangle: 'never',
        braceStyle: '1tbs'
      }
    }
  },

  pwa: {
    registerType: 'autoUpdate',
    injectRegister: 'auto',
    strategies: 'injectManifest',
    srcDir: '.',
    filename: 'sw.ts',
    manifest: {
      name: 'TaskFlow',
      short_name: 'TaskFlow',
      description: 'Gestiona tareas, equipos y espacios de trabajo',
      lang: 'es',
      display: 'standalone',
      start_url: '/',
      theme_color: '#2563eb',
      background_color: '#0b1220',
      icons: [
        { src: '/pwa-192x192.png', sizes: '192x192', type: 'image/png' },
        { src: '/pwa-512x512.png', sizes: '512x512', type: 'image/png' },
        { src: '/pwa-maskable-512x512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' }
      ]
    },
    workbox: {
      globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}']
    },
    devOptions: {
      enabled: true
    }
  }
})
