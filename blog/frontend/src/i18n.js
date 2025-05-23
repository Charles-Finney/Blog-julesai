import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';

// We will load translations from JSON files.
// For this simple setup, we can import them directly.
// For larger apps, HttpBackend might be used.
import translationEN from './locales/en/translation.json';
import translationFR from './locales/fr/translation.json';

const resources = {
  en: {
    translation: translationEN
  },
  fr: {
    translation: translationFR
  }
};

i18n
  // Detect user language
  // Learn more: https://github.com/i18next/i18next-browser-languageDetector
  .use(LanguageDetector)
  // Pass the i18n instance to react-i18next.
  .use(initReactI18next)
  // Init i18next
  // For all options read: https://www.i18next.com/overview/configuration-options
  .init({
    debug: true, // Set to false in production
    fallbackLng: 'en', // Fallback language if detected language is not available
    defaultNS: 'translation', // Default namespace
    resources: resources,
    interpolation: {
      escapeValue: false, // Not needed for React as it escapes by default
    },
    // Language detection options
    detection: {
      // Order and from where user language should be detected
      order: ['querystring', 'cookie', 'localStorage', 'sessionStorage', 'navigator', 'htmlTag', 'path', 'subdomain'],
      // Keys or params to lookup language from
      lookupQuerystring: 'lng',
      lookupCookie: 'i18next',
      lookupLocalStorage: 'i18nextLng',
      lookupSessionStorage: 'i18nextLng',
      // Cache user language on
      caches: ['localStorage', 'cookie'],
      // Optional html tag attribute to lookup language from, example <html lang="en">
      htmlTag: document.documentElement
    }
  });

export default i18n;
