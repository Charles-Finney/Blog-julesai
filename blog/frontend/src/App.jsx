import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import './App.css';

// Import page components
import NewHomePage from './components/NewHomePage'; // Renamed HomePage to NewHomePage for clarity
import SoftwarePage from './components/SoftwarePage';
import BooksPage from './components/BooksPage';
import VideoTutorialsPage from './components/VideoTutorialsPage';
import BlogPage from './components/BlogPage';
// import DocumentsPage from './components/DocumentsPage'; // Phasing out for now
import AboutPage from './components/AboutPage';
import ContactPage from './components/ContactPage';
import LoginPage from './components/LoginPage';
import RegisterPage from './components/RegisterPage';
import UserProfilePage from './components/UserProfilePage';

// Import NavigationBar component
import NavigationBar from './components/NavigationBar';

// Old HomePage (with software list and carousel) might be repurposed for /software or another route later
// For now, it's not directly used in the main routing to avoid confusion with NewHomePage.
// import OldHomePage from './components/HomePage'; 


function App() {
  // Basic layout: Header (NavigationBar) and Main Content
  const mainContentStyle = {
    padding: '20px',
    marginTop: '60px', // Adjust if NavigationBar height changes, to prevent overlap
    // A more robust solution would involve getting actual nav height or using CSS Grid/Flexbox for layout
  };

  return (
    <Router>
      <NavigationBar /> {/* NavigationBar is outside Routes to be persistent */}
      <main style={mainContentStyle}> {/* Using <main> for semantic HTML */}
        <Routes>
          <Route path="/" element={<NewHomePage />} />
          <Route path="/software" element={<SoftwarePage />} />
          <Route path="/books" element={<BooksPage />} />
          <Route path="/videos" element={<VideoTutorialsPage />} />
          <Route path="/blog" element={<BlogPage />} />
          {/* <Route path="/documents" element={<DocumentsPage />} /> */} {/* Documents page route removed */}
          <Route path="/about" element={<AboutPage />} />
          <Route path="/contact" element={<ContactPage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />
          <Route path="/profile" element={<UserProfilePage />} />
          {/* Optional: Route for the old HomePage content if needed for reference or direct access */}
          {/* <Route path="/old-home" element={<OldHomePage />} /> */}
        </Routes>
      </main>
    </Router>
  );
}

export default App;
