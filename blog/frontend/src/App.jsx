import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import './App.css';

// Import page components
import HomePage from './components/HomePage';
import ProjectsPage from './components/ProjectsPage';
import DocumentsPage from './components/DocumentsPage';
import AboutPage from './components/AboutPage';
import ContactPage from './components/ContactPage';

// Import NavigationBar component
import NavigationBar from './components/NavigationBar';

function App() {
  return (
    <Router>
      <div>
        <NavigationBar />
        <div style={{ padding: '20px' }}> {/* Basic styling for page content area */}
          <Routes>
            <Route path="/" element={<HomePage />} />
            <Route path="/projects" element={<ProjectsPage />} />
            <Route path="/documents" element={<DocumentsPage />} />
            <Route path="/about" element={<AboutPage />} />
            <Route path="/contact" element={<ContactPage />} />
          </Routes>
        </div>
      </div>
    </Router>
  );
}

export default App;
