import React, { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

function RegisterPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    username: '',
    email: '',
    password: '',
    confirmPassword: '',
    name: '', // Optional
    profilePictureUrl: '', // Optional
  });

  const [error, setError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prevState => ({
      ...prevState,
      [name]: value,
    }));
  };

  const validateForm = () => {
    if (!formData.username || !formData.email || !formData.password || !formData.confirmPassword) {
      setError(t('errorAllRequiredFields', 'Username, Email, Password, and Confirm Password are required.'));
      return false;
    }
    if (!/^\S+@\S+\.\S+$/.test(formData.email)) {
      setError(t('contactValidationEmail')); // Re-use: "Please enter a valid email address."
      return false;
    }
    if (formData.password.length < 8) {
      setError(t('errorPasswordTooShort', 'Password must be at least 8 characters long.'));
      return false;
    }
    if (formData.password !== formData.confirmPassword) {
      setError(t('errorPasswordMismatch', 'Passwords do not match.'));
      return false;
    }
    setError(''); // Clear any previous errors
    return true;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSuccessMessage('');
    setError('');

    if (!validateForm()) {
      return;
    }

    setIsLoading(true);

    try {
      const postData = new URLSearchParams();
      postData.append('username', formData.username);
      postData.append('email', formData.email);
      postData.append('password', formData.password);
      if (formData.name) postData.append('name', formData.name);
      // Ensure profilePictureUrl is only sent if not empty, otherwise backend might store empty string
      if (formData.profilePictureUrl) postData.append('profile_picture_url', formData.profilePictureUrl);
      
      const response = await axios.post('/blog/backend/api/auth/register.php', postData);

      if (response.data && response.data.status === 'success') {
        setSuccessMessage(t('registrationSuccessMessage', 'Registration successful! You can now log in.'));
        setFormData({ username: '', email: '', password: '', confirmPassword: '', name: '', profilePictureUrl: '' });
        setTimeout(() => {
          navigate('/login');
        }, 2000); 
      } else {
        // This case might be rare if backend uses HTTP error codes for all failures
        setError(response.data.message || t('registrationFailedError', 'Registration failed. Please try again.'));
      }
    } catch (err) {
      if (err.response && err.response.data && err.response.data.message) {
        setError(err.response.data.message); // Use backend's error message
      } else {
        setError(t('registrationFailedError', 'Registration failed. Please try again.'));
      }
      console.error('Registration API error:', err);
    } finally {
      setIsLoading(false);
    }
  };
  
  const pageStyle = { maxWidth: '500px', margin: '40px auto', padding: '30px', border: '1px solid #ddd', borderRadius: '8px', boxShadow: '0 4px 12px rgba(0,0,0,0.1)', backgroundColor: '#fff' };
  const formGroupStyle = { marginBottom: '20px' };
  const labelStyle = { display: 'block', marginBottom: '8px', fontWeight: 'bold', color: '#333' };
  const inputStyle = { width: '100%', padding: '12px', border: '1px solid #ccc', borderRadius: '4px', boxSizing: 'border-box', fontSize: '1em' };
  const buttonStyle = { width: '100%', padding: '12px 15px', backgroundColor: '#28a745', color: 'white', border: 'none', borderRadius: '4px', fontSize: '1.1em', cursor: 'pointer', opacity: isLoading ? 0.7 : 1, transition: 'background-color 0.3s ease' };
  const errorStyle = { color: '#dc3545', marginBottom: '15px', textAlign: 'center', padding: '10px', border: '1px solid #f5c6cb', borderRadius: '4px', backgroundColor: '#f8d7da' };
  const successStyle = { color: '#155724', marginBottom: '15px', textAlign: 'center', padding: '10px', border: '1px solid #c3e6cb', borderRadius: '4px', backgroundColor: '#d4edda' };

  return (
    <div style={pageStyle}>
      <h1 style={{ textAlign: 'center', marginBottom: '25px', color: '#333' }}>{t('registerPageTitle')}</h1>
      {error && <p style={errorStyle}>{error}</p>}
      {successMessage && <p style={successStyle}>{successMessage}</p>}
      <form onSubmit={handleSubmit}>
        <div style={formGroupStyle}>
          <label htmlFor="username" style={labelStyle}>{t('usernameLabel', 'Username')}</label>
          <input type="text" name="username" id="username" value={formData.username} onChange={handleInputChange} style={inputStyle} required />
        </div>
        <div style={formGroupStyle}>
          <label htmlFor="email" style={labelStyle}>{t('emailLabel', 'Email')}</label>
          <input type="email" name="email" id="email" value={formData.email} onChange={handleInputChange} style={inputStyle} required />
        </div>
        <div style={formGroupStyle}>
          <label htmlFor="password" style={labelStyle}>{t('passwordLabel', 'Password')}</label>
          <input type="password" name="password" id="password" value={formData.password} onChange={handleInputChange} style={inputStyle} required />
        </div>
        <div style={formGroupStyle}>
          <label htmlFor="confirmPassword" style={labelStyle}>{t('confirmPasswordLabel', 'Confirm Password')}</label>
          <input type="password" name="confirmPassword" id="confirmPassword" value={formData.confirmPassword} onChange={handleInputChange} style={inputStyle} required />
        </div>
        <div style={formGroupStyle}>
          <label htmlFor="name" style={labelStyle}>{t('nameLabel', 'Name (Optional)')}</label>
          <input type="text" name="name" id="name" value={formData.name} onChange={handleInputChange} style={inputStyle} />
        </div>
        <div style={formGroupStyle}>
          <label htmlFor="profilePictureUrl" style={labelStyle}>{t('profilePictureUrlLabel', 'Profile Picture URL (Optional)')}</label>
          <input type="url" name="profilePictureUrl" id="profilePictureUrl" value={formData.profilePictureUrl} onChange={handleInputChange} style={inputStyle} placeholder="https://example.com/image.png" />
        </div>
        <button type="submit" style={buttonStyle} disabled={isLoading}
          onMouseEnter={e => { if (!isLoading) e.currentTarget.style.backgroundColor = '#218838'; }}
          onMouseLeave={e => { if (!isLoading) e.currentTarget.style.backgroundColor = '#28a745'; }}
        >
          {isLoading ? t('loading', 'Loading...') : t('registerButton', 'Register')}
        </button>
      </form>
    </div>
  );
}

export default RegisterPage;
