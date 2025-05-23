import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useTranslation } from 'react-i18next'; // Import useTranslation

function HomePage() {
  const { t } = useTranslation(); // Initialize useTranslation
  const [clickCount, setClickCount] = useState(t('loading')); // Use t() for initial state
  const pageIdentifier = 'homepage_counter';

  // Function to fetch the initial count
  const fetchInitialCount = async () => {
    try {
      // Using relative path due to Vite proxy
      const response = await axios.get(`/blog/backend/api/counter/click_counter.php?page_identifier=${pageIdentifier}`);
      if (response.data && response.data.status === 'success') {
        setClickCount(response.data.count);
      } else {
        setClickCount(t('errorLoadingCount')); // Use t()
        console.error('Failed to fetch count:', response.data.message);
      }
    } catch (error) {
      setClickCount(t('errorLoadingCount')); // Use t()
      console.error('API call error (fetchInitialCount):', error);
    }
  };

  // Function to increment the count
  const incrementCount = async () => {
    try {
      // Using relative path due to Vite proxy
      // For POST requests with axios and form data, URLSearchParams is a good way
      const params = new URLSearchParams();
      params.append('page_identifier', pageIdentifier);

      const response = await axios.post(`/blog/backend/api/counter/click_counter.php`, params);
      
      if (response.data && response.data.status === 'success') {
        setClickCount(response.data.count);
      } else {
        // Optionally, handle specific errors or display a message to the user
        console.error('Failed to increment count:', response.data.message);
      }
    } catch (error) {
      console.error('API call error (incrementCount):', error);
      // Optionally, inform the user that the increment failed
    }
  };

  // Fetch initial count on component mount
  useEffect(() => {
    fetchInitialCount();
  }, []); // Empty dependency array ensures this runs only once on mount

  return (
    <div>
      <h1>{t('homeWelcome')}</h1>
      <p style={{ fontSize: '1.2em', margin: '20px 0' }}>
        {t('pageClicks')}: <span style={{ fontWeight: 'bold', color: '#007bff' }}>{clickCount}</span>
      </p>
      <button 
        onClick={incrementCount} 
        style={{
          padding: '10px 20px',
          fontSize: '1em',
          color: 'white',
          backgroundColor: '#007bff',
          border: 'none',
          borderRadius: '5px',
          cursor: 'pointer'
        }}
      >
        {t('clickMe')}
      </button>
    </div>
  );
}

export default HomePage;
