import React, { useState } from 'react';
import axios from 'axios';
import { useTranslation } from 'react-i18next'; // Import useTranslation

function ContactPage() {
  const { t } = useTranslation(); // Initialize useTranslation
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [message, setMessage] = useState('');
  const [submissionStatus, setSubmissionStatus] = useState(''); // '', 'success', 'error'
  const [validationError, setValidationError] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setValidationError('');
    setSubmissionStatus('');

    // Basic client-side validation
    if (!name.trim() || !message.trim()) {
      setValidationError(t('contactValidationRequired')); // Use t()
      return;
    }
    // Optional: Email validation regex (simple one)
    if (email.trim() && !/^\S+@\S+\.\S+$/.test(email)) {
        setValidationError(t('contactValidationEmail')); // Use t()
        return;
    }

    try {
      const params = new URLSearchParams();
      params.append('name', name);
      params.append('email', email);
      params.append('message', message);

      // Using relative path due to Vite proxy
      const response = await axios.post('/blog/backend/api/contact/receive_message.php', params);

      if (response.data && response.data.status === 'success') {
        setSubmissionStatus('success');
        setName('');
        setEmail('');
        setMessage('');
      } else {
        setSubmissionStatus('error'); // Keep 'error' as a status key
        console.error('Failed to send message:', response.data.message);
      }
    } catch (error) {
      setSubmissionStatus('error'); // Keep 'error' as a status key
      console.error('API call error (handleSubmit):', error);
    }
  };

  // Basic inline styles for demonstration
  const formStyle = {
    display: 'flex',
    flexDirection: 'column',
    maxWidth: '500px',
    margin: '0 auto',
  };
  const inputStyle = {
    marginBottom: '10px',
    padding: '8px',
    fontSize: '1em',
    borderRadius: '4px',
    border: '1px solid #ccc',
  };
  const buttonStyle = {
    padding: '10px 20px',
    fontSize: '1em',
    color: 'white',
    backgroundColor: '#007bff',
    border: 'none',
    borderRadius: '5px',
    cursor: 'pointer',
    marginTop: '10px',
  };
   const feedbackStyle = {
    marginTop: '15px',
    padding: '10px',
    borderRadius: '4px',
    textAlign: 'center',
  };
  const successStyle = { ...feedbackStyle, backgroundColor: '#d4edda', color: '#155724' };
  const errorStyle = { ...feedbackStyle, backgroundColor: '#f8d7da', color: '#721c24' };
  const validationErrorStyle = { ...feedbackStyle, backgroundColor: '#fff3cd', color: '#856404', marginBottom: '10px' };

  const whatsappButtonStyle = {
    ...buttonStyle,
    backgroundColor: '#25D366', // WhatsApp green
    marginRight: '10px',
    textDecoration: 'none', // For Link/a tag appearance
    display: 'inline-block', // For Link/a tag appearance
  };


  return (
    <div>
      <h1>{t('contactTitle')}</h1>
      <form onSubmit={handleSubmit} style={formStyle}>
        <label htmlFor="name">{t('contactFormName')}</label>
        <input
          type="text"
          id="name"
          value={name}
          onChange={(e) => setName(e.target.value)}
          style={inputStyle}
          required
        />

        <label htmlFor="email">{t('contactFormEmail')}</label>
        <input
          type="email"
          id="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          style={inputStyle}
        />

        <label htmlFor="message">{t('contactFormMessage')}</label>
        <textarea
          id="message"
          value={message}
          onChange={(e) => setMessage(e.target.value)}
          rows="5"
          style={inputStyle}
          required
        ></textarea>

        {validationError && <p style={validationErrorStyle}>{validationError}</p>}
        
        <button type="submit" style={buttonStyle}>{t('contactFormSend')}</button>
      </form>

      {submissionStatus === 'success' && (
        <p style={successStyle}>{t('contactSuccess')}</p>
      )}
      {submissionStatus === 'error' && (
        <p style={errorStyle}>{t('contactError')}</p>
      )}

      <div style={{ marginTop: '30px', textAlign: 'center' }}>
        <h2>{t('contactOtherWays')}</h2>
        <a 
          href="https://wa.me/yourphonenumber" // Replace with actual number
          target="_blank" 
          rel="noopener noreferrer"
          style={whatsappButtonStyle}
        >
          {t('contactWhatsAppMsg')}
        </a>
        <a 
          href="https://wa.me/yourphonenumber?call=true" // Replace with actual number, call links less reliable
          target="_blank" 
          rel="noopener noreferrer"
          style={whatsappButtonStyle}
        >
          {t('contactWhatsAppCall')}
        </a>
        {/* Placeholder for direct call link if preferred, though WhatsApp is specified */}
        {/* <a href="tel:yourphonenumber" style={buttonStyle}>Call Directly</a> */}
      </div>
    </div>
  );
}

export default ContactPage;
