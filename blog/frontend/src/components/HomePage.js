import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useTranslation } from 'react-i18next';
import { Carousel } from 'react-responsive-carousel'; // Import Carousel
import "react-responsive-carousel/lib/styles/carousel.min.css"; // Import Carousel CSS

function HomePage() {
  const { t, i18n } = useTranslation();
  const [clickCount, setClickCount] = useState(t('loading'));
  const pageIdentifier = 'homepage_counter';

  const [softwareProjects, setSoftwareProjects] = useState([]);
  const [selectedProjectId, setSelectedProjectId] = useState(null);
  const [isLoadingProjects, setIsLoadingProjects] = useState(false);
  const [projectsError, setProjectsError] = useState(null);

  // State for selected project details
  const [selectedProjectDetails, setSelectedProjectDetails] = useState(null);
  const [isLoadingDetails, setIsLoadingDetails] = useState(false);
  const [detailsError, setDetailsError] = useState(null);


  // Fetch click count
  useEffect(() => {
    const fetchInitialCount = async () => {
      try {
        const response = await axios.get(`/blog/backend/api/counter/click_counter.php?page_identifier=${pageIdentifier}`);
        if (response.data && response.data.status === 'success') {
          setClickCount(response.data.count);
        } else {
          setClickCount(t('errorLoadingCount'));
          console.error('Failed to fetch count:', response.data.message);
        }
      } catch (error) {
        setClickCount(t('errorLoadingCount'));
        console.error('API call error (fetchInitialCount):', error);
      }
    };
    fetchInitialCount();
  }, [t, pageIdentifier]); // Added t and pageIdentifier to dependencies

  // Increment click count
  const incrementCount = async () => {
    try {
      const params = new URLSearchParams();
      params.append('page_identifier', pageIdentifier);
      const response = await axios.post(`/blog/backend/api/counter/click_counter.php`, params);
      if (response.data && response.data.status === 'success') {
        setClickCount(response.data.count);
      } else {
        console.error('Failed to increment count:', response.data.message);
      }
    } catch (error) {
      console.error('API call error (incrementCount):', error);
    }
  };

  // Fetch software projects
  useEffect(() => {
    const fetchSoftwareProjects = async () => {
      setIsLoadingProjects(true);
      setProjectsError(null);
      const currentLanguage = i18n.language.split('-')[0];
      try {
        // Fetching posts of type 'software'
        const response = await axios.get(`/blog/backend/api/posts/read_posts.php?type=software&lang=${currentLanguage}`);
        if (response.data && response.data.status === 'success') {
          setSoftwareProjects(response.data.data || []);
        } else {
          setProjectsError(t('postsError')); 
          console.error('Failed to fetch software projects:', response.data ? response.data.message : 'No response data');
        }
      } catch (err) {
        setProjectsError(`${t('postsError')} ${err.message}`);
        console.error('API call error (fetchSoftwareProjects):', err);
      } finally {
        setIsLoadingProjects(false);
      }
    };
    fetchSoftwareProjects();
  }, [i18n.language, t]); // Re-fetch if language or t function changes

  // Fetch project details when selectedProjectId changes
  useEffect(() => {
    const fetchProjectDetails = async (projectId) => {
      if (!projectId) {
        setSelectedProjectDetails(null); // Clear details if no project is selected
        return;
      }
      setIsLoadingDetails(true);
      setDetailsError(null);
      const currentLanguage = i18n.language.split('-')[0];
      try {
        const response = await axios.get(`/blog/backend/api/projects/read_project_details.php?post_id=${projectId}&lang=${currentLanguage}`);
        if (response.data && response.data.status === 'success') {
          setSelectedProjectDetails(response.data.data);
        } else {
          setDetailsError(t('postsError')); // Or a more specific error message
          console.error('Failed to fetch project details:', response.data ? response.data.message : 'No response data');
        }
      } catch (err) {
        setDetailsError(`${t('postsError')} ${err.message}`);
        console.error('API call error (fetchProjectDetails):', err);
      } finally {
        setIsLoadingDetails(false);
      }
    };

    fetchProjectDetails(selectedProjectId);
  }, [selectedProjectId, i18n.language, t]);


  const handleProjectClick = (projectId) => {
    setSelectedProjectId(projectId);
    // No need to call fetchProjectDetails here, useEffect will handle it
  };

  // Refined inline styles
  const styles = {
    homePage: {
      padding: '20px',
      fontFamily: 'Arial, sans-serif',
    },
    clickerSection: {
      textAlign: 'center',
      padding: '20px',
      borderBottom: '1px solid #ddd',
      marginBottom: '30px',
    },
    primaryButton: {
      padding: '10px 20px',
      fontSize: '1em',
      color: 'white',
      backgroundColor: '#007bff',
      border: 'none',
      borderRadius: '5px',
      cursor: 'pointer',
      transition: 'background-color 0.3s ease',
    },
    // Note: Hover effect for primaryButton (e.g., backgroundColor: '#0056b3') would typically be done with CSS classes.
    layout: {
      display: 'flex',
      flexDirection: 'row', // Default for larger screens. For smaller screens, change to 'column' via media query.
      gap: '30px',
      marginTop: '20px',
      // Conceptual Media Query: @media (max-width: 768px) { flexDirection: 'column'; }
    },
    projectListContainer: {
      flex: '1',
      border: '1px solid #ddd',
      padding: '20px',
      borderRadius: '8px',
      backgroundColor: '#f9f9f9',
      maxHeight: '600px',
      overflowY: 'auto',
    },
    projectListTitle: {
      textAlign: 'center',
      fontSize: '1.5em',
      marginBottom: '15px',
      color: '#333',
    },
    projectItem: {
      padding: '12px 15px',
      margin: '8px 0',
      border: '1px solid #ccc',
      borderRadius: '4px',
      cursor: 'pointer',
      backgroundColor: '#fff',
      textAlign: 'left',
      transition: 'background-color 0.2s ease, box-shadow 0.2s ease',
      boxShadow: '0 1px 3px rgba(0,0,0,0.05)',
    },
    // Note: Hover effect for projectItem (e.g., backgroundColor: '#f0f0f0', boxShadow: '0 2px 5px rgba(0,0,0,0.1)') would use CSS classes.
    selectedProjectItem: { // Style for the selected project item
      backgroundColor: '#d6eaff', // Light blue to indicate selection
      boxShadow: '0 2px 5px rgba(0,0,0,0.1)',
      fontWeight: 'bold',
    },
    selectedProjectDisplay: {
      flex: '2.5',
      border: '1px solid #ddd',
      padding: '20px',
      borderRadius: '8px',
      backgroundColor: '#fff',
      minHeight: '400px',
      display: 'flex',
      flexDirection: 'column',
    },
    projectDetailTitle: {
      textAlign: 'center',
      fontSize: '1.8em',
      color: '#222',
      marginBottom: '20px',
    },
    projectDetailContent: {
      marginTop: '25px',
      fontSize: '1em',
      lineHeight: '1.6',
      whiteSpace: 'pre-wrap',
      color: '#444',
    },
    placeholderText: {
      textAlign: 'center',
      fontSize: '1.1em',
      color: '#777',
      marginTop: '50px',
    },
    carouselContainer: {
      marginBottom: '20px',
    },
    carouselImageContainer: {
      maxHeight: '450px',
    },
    carouselImage: {
      objectFit: 'contain',
      maxHeight: '100%',
      maxWidth: '100%',
      borderRadius: '4px',
    },
    carouselLegend: {
      fontSize: '0.9em',
      backgroundColor: 'rgba(0,0,0,0.65)',
      color: '#fff',
      padding: '8px',
      borderRadius: '0 0 4px 4px',
    },
    loadingErrorText: { // Unified style for loading/error in project list/details
      fontSize: '1em',
      color: '#555', // Neutral color for loading, error color will override if needed
      textAlign: 'center',
      padding: '10px',
    },
  };

  return (
    <div style={styles.homePage}>
      <div style={styles.clickerSection}>
        <h1>{t('homeWelcome')}</h1>
        <p style={{ fontSize: '1.2em', margin: '20px 0' }}>
          {t('pageClicks')}: <span style={{ fontWeight: 'bold', color: '#007bff' }}>{clickCount}</span>
        </p>
        {/* For hover on button: consider adding a className and defining :hover in App.css */}
        <button 
          onClick={incrementCount} 
          style={styles.primaryButton}
          onMouseEnter={e => e.currentTarget.style.backgroundColor = '#0056b3'}
          onMouseLeave={e => e.currentTarget.style.backgroundColor = '#007bff'}
        >
          {t('clickMe')}
        </button>
      </div>

      {/* Conceptual Media Query for layoutStyle: @media (max-width: 768px) { flex-direction: column; } */}
      <div style={styles.layout}>
        <div style={styles.projectListContainer}>
          <h2 style={styles.projectListTitle}>{t('navProjects')}</h2>
          {isLoadingProjects && <p style={styles.loadingErrorText}>{t('postsLoading')}</p>}
          {projectsError && <p style={{ ...styles.loadingErrorText, color: '#cc0000' }}>{projectsError}</p>}
          {!isLoadingProjects && !projectsError && softwareProjects.length === 0 && (
            <p style={styles.loadingErrorText}>{t('postsNoPosts', { postType: 'software' })}</p>
          )}
          {softwareProjects.map(project => {
            const isSelected = selectedProjectId === project.id;
            return (
              <div
                key={project.id}
                style={{
                  ...styles.projectItem,
                  ...(isSelected ? styles.selectedProjectItem : {}),
                }}
                onClick={() => handleProjectClick(project.id)}
                onMouseEnter={e => { if (!isSelected) e.currentTarget.style.backgroundColor = '#f0f0f0'; }}
                onMouseLeave={e => { if (!isSelected) e.currentTarget.style.backgroundColor = '#fff'; }}
              >
                {project.localized_title}
              </div>
            );
          })}
        </div>

        <div style={styles.selectedProjectDisplay}>
          {!selectedProjectId && <p style={styles.placeholderText}>{t('selectProjectPrompt', 'Select a project to view details.')}</p>}
          
          {selectedProjectId && isLoadingDetails && <p style={styles.loadingErrorText}>{t('postsLoading', 'Loading project details...')}</p>}
          
          {selectedProjectId && detailsError && <p style={{ ...styles.loadingErrorText, color: '#cc0000' }}>{detailsError}</p>}

          {selectedProjectId && !isLoadingDetails && !detailsError && selectedProjectDetails && (
            <> {/* Use Fragment to avoid unnecessary div */}
              <h3 style={styles.projectDetailTitle}>{selectedProjectDetails.localized_title}</h3>
              
              {selectedProjectDetails.images && selectedProjectDetails.images.length > 0 ? (
                <div style={styles.carouselContainer}>
                  <Carousel 
                    showArrows={true} 
                    showThumbs={true} 
                    dynamicHeight={false}
                    infiniteLoop={true}
                    useKeyboardArrows={true}
                    emulateTouch={true}
                  >
                    {selectedProjectDetails.images.map(image => (
                      <div key={image.id} style={styles.carouselImageContainer}>
                        <img 
                          src={`/blog/backend/${image.image_url}`}
                          alt={image.localized_description || selectedProjectDetails.localized_title} 
                          style={styles.carouselImage}
                        />
                        {image.localized_description && (
                          <p className="legend" style={styles.carouselLegend}>
                            {image.localized_description}
                          </p>
                        )}
                      </div>
                    ))}
                  </Carousel>
                </div>
              ) : (
                <p style={styles.loadingErrorText}>{t('noImagesForProject', 'No images available for this project.')}</p>
              )}
              
              <div style={styles.projectDetailContent}>
                {selectedProjectDetails.localized_content}
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}

export default HomePage;
