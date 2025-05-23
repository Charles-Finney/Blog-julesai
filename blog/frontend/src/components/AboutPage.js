import React from 'react';
import { useTranslation } from 'react-i18next';

function AboutPage() {
  const { t } = useTranslation();
  return (
    <div>
      <h1>{t('aboutTitle')}</h1>
      {/* Add more translated content here as needed */}
      <p>This is a placeholder page for "About Me". Content to be added.</p>
    </div>
  );
}

export default AboutPage;
