
const metaEnv = import.meta.env

// Merge envs (meta for build envs, window for runtime envs)
const env = { ...window.env, ...metaEnv }

export default {
  // Default envs:
  BASE_URL: env.BASE_URL,
  DEV: env.DEV,
  MODE: env.MODE,
  PROD: env.PROD,
  SSR: env.SSR,
  // Custom envs:
  AUTH_URL: env.FRONTEND_APP_AUTH_URL,
  AUTH_CLIENT_ID: env.FRONTEND_APP_AUTH_CLIENT_ID,
  AUTH_CLIENT_SECRET: env.FRONTEND_APP_AUTH_CLIENT_SECRET,
}
