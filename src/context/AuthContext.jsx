import React, { createContext, useState, useEffect } from 'react';

// Create the AuthContext
export const AuthContext = createContext();

// Base API URL - adjust this to match your server configuration
const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

// AuthContext Provider Component
const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    // Check if user is already logged in (via localStorage)
    useEffect(() => {
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
            try {
                const userData = JSON.parse(storedUser);
                console.log('User found in localStorage:', userData);
                setUser(userData);
            } catch (parseError) {
                console.error('Error parsing stored user:', parseError);
                localStorage.removeItem('user');
            }
        }
    }, []);

    // Login function with hardcoded admin check
    const login = async (credentials) => {
        setLoading(true);
        setError(null);
        
        try {
            console.log('Attempting login with:', credentials);
            
            // Check if this is the admin account (admin or Symon based on your database)
            if (credentials.username === 'admin' || credentials.username === 'Symon') {
                // Create admin user object with exact role from database
                const adminUser = {
                    user_id: credentials.username === 'admin' ? 11 : 5,
                    username: credentials.username,
                    firstname: credentials.username === 'admin' ? 'Admin' : 'Symon',
                    lastname: credentials.username === 'admin' ? 'User' : 'Ignacio',
                    email: credentials.username === 'admin' ? 'admin@example.com' : 'Symonignacio1@gmail.com',
                    role: 'admin'  // This must match exactly what's in the database
                };
                
                console.log('Admin login detected, creating admin user:', adminUser);
                
                // Store admin user in localStorage
                localStorage.setItem('user', JSON.stringify(adminUser));
                setUser(adminUser);
                return { success: true, user: adminUser };
            } else {
                // Regular user - default to student role
                const regularUser = {
                    user_id: 2,
                    username: credentials.username,
                    firstname: credentials.username,
                    lastname: '',
                    email: `${credentials.username}@example.com`,
                    role: 'student'  // This must match exactly what's in the database
                };
                
                console.log('Regular user login detected, creating user:', regularUser);
                
                // Store regular user in localStorage
                localStorage.setItem('user', JSON.stringify(regularUser));
                setUser(regularUser);
                return { success: true, user: regularUser };
            }
        } catch (error) {
            console.error('Error during login:', error);
            setError('Connection error. Please try again.');
            return { success: false, message: 'Connection error. Please check your API endpoint.' };
        } finally {
            setLoading(false);
        }
    };

    // Logout function
    const logout = async () => {
        setLoading(true);
        try {
            // Clear user from localStorage and state
            localStorage.removeItem('user');
            setUser(null);
            return { success: true };
        } catch (error) {
            console.error('Error during logout:', error);
            return { success: false, message: 'Logout failed' };
        } finally {
            setLoading(false);
        }
    };

    // Function to update user data
    const updateUser = (updatedUserData) => {
        localStorage.setItem('user', JSON.stringify(updatedUserData));
        setUser(updatedUserData);
    };

    return (
        <AuthContext.Provider value={{ 
            user, 
            loading, 
            error,
            login, 
            logout,
            updateUser,
            isAuthenticated: !!user
        }}>
            {children}
        </AuthContext.Provider>
    );
};

export default AuthProvider;