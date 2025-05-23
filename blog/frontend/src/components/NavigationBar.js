import React from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next'; // Import useTranslation

import LanguageSwitcher from './LanguageSwitcher'; // Import LanguageSwitcher

function NavigationBar() {
  const { t } = useTranslation(); // Initialize useTranslation

  return (
    <nav style={{ marginBottom: '20px', borderBottom: '1px solid #ccc', paddingBottom: '10px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
      <div>
        <Link to="/" style={{ marginRight: '10px' }}>{t('navHome')}</Link>
        <Link to="/projects" style={{ marginRight: '10px' }}>{t('navProjects')}</Link>
        <Link to="/documents" style={{ marginRight: '10px' }}>{t('navDocuments')}</Link>
        <Link to="/about" style={{ marginRight: '10px' }}>{t('navAbout')}</Link>
        <Link to="/contact">{t('navContact')}</Link>
      </div>
      <LanguageSwitcher /> {/* Add LanguageSwitcher here */}
    </nav>
  );
}

export default NavigationBar;
