import React from 'react';
import { useTranslation } from 'react-i18next';

function LanguageSwitcher() {
  const { i18n, t } = useTranslation();

  const changeLanguage = (lng) => {
    i18n.changeLanguage(lng);
  };

  // Basic styling for the buttons
  const buttonStyle = {
    padding: '5px 10px',
    margin: '0 5px',
    border: '1px solid #ccc',
    borderRadius: '4px',
    cursor: 'pointer',
    backgroundColor: '#f0f0f0',
  };

  const activeButtonStyle = {
    ...buttonStyle,
    backgroundColor: '#007bff',
    color: 'white',
    fontWeight: 'bold',
  };

  return (
    <div>
      <button 
        style={i18n.resolvedLanguage === 'en' ? activeButtonStyle : buttonStyle} 
        onClick={() => changeLanguage('en')}
      >
        {t('languageSwitcherEnglish')}
      </button>
      <button 
        style={i18n.resolvedLanguage === 'fr' ? activeButtonStyle : buttonStyle} 
        onClick={() => changeLanguage('fr')}
      >
        {t('languageSwitcherFrench')}
      </button>
    </div>
  );
}

export default LanguageSwitcher;
