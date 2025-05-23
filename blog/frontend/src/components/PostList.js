import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useTranslation } from 'react-i18next'; // Import useTranslation

function PostList({ postType }) {
  const { t, i18n } = useTranslation(); // Initialize useTranslation
  const [posts, setPosts] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchPosts = async () => {
      setIsLoading(true);
      setError(null);
      // Get current language to pass to backend
      const currentLanguage = i18n.language.split('-')[0]; // 'en-US' -> 'en'

      try {
        // Using relative path due to Vite proxy, template literal for postType, and add lang parameter
        const response = await axios.get(`/blog/backend/api/posts/read_posts.php?type=${postType}&lang=${currentLanguage}`);
        if (response.data && response.data.status === 'success') {
          setPosts(response.data.data || []); // Ensure posts is an array
        } else {
          console.error('Failed to fetch posts:', response.data.message || 'No message provided');
          setError(t('postsError')); // Use t()
          setPosts([]);
        }
      } catch (err) {
        console.error('API call error (fetchPosts):', err);
        setError(`${t('postsError')} ${err.message}`); // Use t() and append specific error
        setPosts([]);
      } finally {
        setIsLoading(false);
      }
    };

    if (postType) {
      fetchPosts();
    } else {
      setPosts([]);
      setIsLoading(false);
    }
  // Re-run effect if postType or language changes
  }, [postType, i18n.language, t]); // Added i18n.language and t to dependencies

  // Basic inline styles for posts and list items
  const listStyle = {
    listStyleType: 'none',
    padding: 0,
  };

  const postItemStyle = {
    border: '1px solid #ddd',
    borderRadius: '5px',
    padding: '15px',
    marginBottom: '15px',
    boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
  };

  const titleStyle = {
    fontSize: '1.5em',
    color: '#333',
    marginBottom: '10px',
  };

  const contentStyle = {
    fontSize: '1em',
    color: '#555',
    whiteSpace: 'pre-wrap', // Preserve whitespace and newlines in content
    marginBottom: '10px', // Add some space before timestamps
  };

  const timestampStyle = {
    fontSize: '0.8em',
    color: '#777',
    marginTop: '5px',
  };

  if (isLoading) {
    return <p>{t('postsLoading')}</p>; // Use t()
  }

  if (error) {
    return <p style={{ color: 'red' }}>{error}</p>; // Error message is already translated or combined with translated string
  }

  if (posts.length === 0) {
    // Use t() with interpolation for postType
    return <p>{t('postsNoPosts', { postType: postType })}</p>; 
  }

  return (
    <ul style={listStyle}>
      {posts.map(post => (
        <li key={post.id} style={postItemStyle}>
          {/* Use localized_title and localized_content from API response */}
          <h3 style={titleStyle}>{post.localized_title}</h3>
          <div style={contentStyle}>{post.localized_content}</div>
          <div style={timestampStyle}>
            {t('postCreated')}: {new Date(post.created_at).toLocaleString()}
          </div>
          {new Date(post.updated_at).getTime() - new Date(post.created_at).getTime() > 1000 && (
            <div style={timestampStyle}>
              {t('postUpdated')}: {new Date(post.updated_at).toLocaleString()}
            </div>
          )}
        </li>
      ))}
    </ul>
  );
}

export default PostList;
