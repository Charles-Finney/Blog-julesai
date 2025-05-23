import React, { Suspense } from 'react'; // Import Suspense
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import './index.css';
import App from './App.jsx';
import './i18n'; // Import i18n configuration

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <Suspense fallback={<div>Loading translations...</div>}> {/* Basic fallback UI */}
      <App />
    </Suspense>
  </StrictMode>,
);
