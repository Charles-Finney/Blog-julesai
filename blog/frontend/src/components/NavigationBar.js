import React from 'react'; // Removed useState
import { Link, useNavigate } from 'react-router-dom'; // Added useNavigate
import { useTranslation } from 'react-i18next';
import LanguageSwitcher from './LanguageSwitcher';
import { useAuth } from '../context/AuthContext'; // Import useAuth

function NavigationBar() {
  const { t } = useTranslation();
  const { isAuthenticated, currentUser, logout, isLoading } = useAuth(); // Use AuthContext
  const navigate = useNavigate();

  const handleLogout = async () => {
    // Perform actual API call for logout if needed
    // For now, just updating context state and localStorage
    // Example: await axios.post('/api/auth/logout'); 
    logout(); // Clears context and localStorage
    navigate('/'); // Redirect to homepage after logout
  };

  const navStyle = {
    marginBottom: '20px',
    borderBottom: '1px solid #ddd',
    paddingBottom: '15px',
    paddingTop: '15px',
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: '#f8f9fa', // Light background for the nav
    paddingLeft: '20px',
    paddingRight: '20px',
  };

  const linkGroupStyle = {
    display: 'flex',
    alignItems: 'center',
    gap: '15px', // Spacing between link groups
  };

  const linkStyle = {
    marginRight: '15px',
    textDecoration: 'none',
    color: '#007bff', // Primary link color
    fontWeight: '500',
  };
  
  // Style for auth links when they are grouped
  const authLinkStyle = { ...linkStyle, color: '#28a745' }; // Green for login/register
  const logoutLinkStyle = { ...linkStyle, color: '#dc3545', cursor: 'pointer' }; // Red for logout

  // Don't render auth links if still loading initial auth state
  // This prevents a flash of "Login/Register" if user is actually logged in from localStorage
  const renderAuthLinks = () => {
    if (isLoading) {
      return null; // Or a loading spinner/placeholder for that section
    }
    if (isAuthenticated) {
      return (
        <>
          <Link to="/profile" style={linkStyle}>
            {currentUser?.username ? `${t('navProfile')} (${currentUser.username})` : t('navProfile')}
          </Link>
          <span onClick={handleLogout} style={logoutLinkStyle} role="button" tabIndex={0}>
            {t('navLogout')}
          </span>
        </>
      );
    } else {
      return (
        <>
          <Link to="/login" style={authLinkStyle}>{t('navLogin')}</Link>
          <Link to="/register" style={authLinkStyle}>{t('navRegister')}</Link>
        </>
      );
    }
  };

  return (
    <nav style={navStyle}>
      <div style={linkGroupStyle}> {/* Group for main navigation links */}
        <Link to="/" style={linkStyle}>{t('navHome')}</Link>
        <Link to="/software" style={linkStyle}>{t('navSoftware')}</Link>
        <Link to="/books" style={linkStyle}>{t('navBooks')}</Link>
        <Link to="/videos" style={linkStyle}>{t('navVideos')}</Link>
        <Link to="/blog" style={linkStyle}>{t('navBlog')}</Link>
        <Link to="/contact" style={linkStyle}>{t('navContact')}</Link>
        <Link to="/about" style={linkStyle}>{t('navAbout')}</Link>
      </div>

      <div style={linkGroupStyle}> {/* Group for language switcher and auth links */}
        <LanguageSwitcher />
        {renderAuthLinks()}
      </div>
    </nav>
  );
}

export default NavigationBar;
