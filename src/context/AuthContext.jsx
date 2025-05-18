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

    // Check if user is already logged in (via session)
    useEffect(() => {
        const checkAuthStatus = async () => {
            try {
                console.log('Checking auth status...');
                
                // First test the API connectivity
                try {
                    const testResponse = await fetch(`${API_BASE_URL}/test.php`);
                    const testData = await testResponse.json();
                    console.log('API test response:', testData);
                } catch (testError) {
                    console.error('API test failed:', testError);
                }
                
                const response = await fetch(`${API_BASE_URL}/getUser.php`, {
                    method: 'GET',
                    credentials: 'include', // Include cookies for session handling
                });

                console.log('getUser response status:', response.status);
                
                const data = await response.json();
                console.log('getUser response data:', data);
                
                if (data.success && data.user) {
                    console.log('User already logged in:', data.user);
                    setUser(data.user);
                }
            } catch (error) {
                console.error('Error checking auth status:', error);
            }
        };

        checkAuthStatus();
    }, []);

    // Login function that connects to your actual database
    const login = async (credentials) => {
        setLoading(true);
        setError(null);
        
        try {
            console.log('Attempting login with:', credentials);
            console.log('API URL:', `${API_BASE_URL}/login.php`);
            
            const response = await fetch(`${API_BASE_URL}/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(credentials),
                credentials: 'include', // Include cookies for session handling
            });
            
            console.log('Response status:', response.status);
            
            const text = await response.text();
            console.log('Raw response:', text);
            
            try {
                const data = JSON.parse(text);
                
                if (data.success) {
                    console.log('Login successful:', data.user);
                    setUser(data.user);
                    return { success: true, user: data.user };
                } else {
                    console.error('Login failed:', data.message);
                    setError(data.message || 'Login failed');
                    return { success: false, message: data.message || 'Login failed' };
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                setError('Invalid response from server');
                return { success: false, message: 'Invalid response from server' };
            }
        } catch (error) {
            console.error('Error during login:', error);
            setError('Connection error. Please try again.');
            return { success: false, message: 'Connection error. Please check your API endpoint.' };
        } finally {
            setLoading(false);
        }
    };

    const logout = async () => {
        setLoading(true);
        try {
            // Call logout API if available
            try {
                await fetch(`${API_BASE_URL}/logout.php`, {
                    method: 'POST',
                    credentials: 'include', // Include cookies for session handling
                });
            } catch (error) {
                console.error('Error calling logout API:', error);
            }
            
            // Clear user from state
            setUser(null);
            
            return { success: true };
        } catch (error) {
            console.error('Error during logout:', error);
            return { success: false, message: 'Logout failed' };
        } finally {
            setLoading(false);
        }
    };

    return (
        <AuthContext.Provider value={{ 
            user, 
            loading, 
            error,
            login, 
            logout,
            isAuthenticated: !!user
        }}>
            {children}
        </AuthContext.Provider>
    );
};

export default AuthProvider;