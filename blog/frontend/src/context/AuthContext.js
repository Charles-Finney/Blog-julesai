import React, { createContext, useState, useContext, useEffect } from 'react';

// 1. Define AuthContext
const AuthContext = createContext(null);

// 2. Create AuthProvider component
export const AuthProvider = ({ children }) => {
  const [currentUser, setCurrentUser] = useState(null);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [isLoading, setIsLoading] = useState(true); // To handle initial check from localStorage

  // Optional: Initialize state from localStorage for simple persistence
  useEffect(() => {
    try {
      const storedUser = localStorage.getItem('currentUser');
      if (storedUser) {
        const parsedUser = JSON.parse(storedUser);
        setCurrentUser(parsedUser);
        setIsAuthenticated(true);
      }
    } catch (error) {
      // If JSON parsing fails or localStorage is inaccessible
      console.error("Error loading user from localStorage:", error);
      localStorage.removeItem('currentUser'); // Clean up potentially corrupted item
    }
    setIsLoading(false); // Done loading initial state
  }, []);

  const login = (userData) => {
    // This function will be called after a successful API login.
    // userData should be the user object received from the backend.
    setCurrentUser(userData);
    setIsAuthenticated(true);
    try {
      localStorage.setItem('currentUser', JSON.stringify(userData));
    } catch (error) {
      console.error("Error saving user to localStorage:", error);
    }
  };

  const logout = () => {
    // This function will be called after a successful API logout or by user action.
    setCurrentUser(null);
    setIsAuthenticated(false);
    try {
      localStorage.removeItem('currentUser');
    } catch (error) {
      console.error("Error removing user from localStorage:", error);
    }
    // Optionally, redirect to login page or homepage
    // This might be better handled in the component calling logout.
  };

  const value = {
    currentUser,
    isAuthenticated,
    isLoading, // Provide isLoading so consumers can wait for localStorage check
    login,
    logout
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};

// 3. Create useAuth custom hook
export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined && process.env.NODE_ENV !== 'production') {
    // In development, throw an error if useAuth is used outside of AuthProvider.
    // In production, context might be undefined if AuthProvider is conditionally rendered
    // or if there's a packaging issue. A more graceful fallback might be needed
    // depending on how strictly the app relies on auth context.
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
