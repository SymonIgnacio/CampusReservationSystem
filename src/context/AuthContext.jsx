import React, { createContext, useState, useEffect } from 'react';
import { onAuthStateChanged, signOut } from 'firebase/auth';
import { auth } from '../firebase';

// Create the AuthContext
export const AuthContext = createContext();

// AuthContext Provider Component
const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    // Check Firebase auth state
    useEffect(() => {
        const unsubscribe = onAuthStateChanged(auth, async (firebaseUser) => {
            if (firebaseUser && (firebaseUser.emailVerified || firebaseUser.email === 'admin@example.com')) {
                // Create user object from Firebase data
                const userData = {
                    user_id: firebaseUser.uid,
                    username: firebaseUser.email.split('@')[0],
                    firstname: firebaseUser.email === 'admin@example.com' ? 'Admin' : 'User',
                    lastname: firebaseUser.email === 'admin@example.com' ? 'User' : '',
                    email: firebaseUser.email,
                    role: firebaseUser.email === 'admin@example.com' ? 'admin' : 'user',
                    firebase_uid: firebaseUser.uid
                };
                console.log('Firebase user authenticated:', userData);
                setUser(userData);
            } else {
                setUser(null);
            }
        });

        return () => unsubscribe();
    }, []);

    // Login function for Firebase
    const login = async ({ firebaseUser }) => {
        console.log('FIREBASE LOGIN FUNCTION CALLED with:', firebaseUser?.email);
        setLoading(true);
        setError(null);
        
        try {
            if (firebaseUser) {
                console.log('Firebase user email:', firebaseUser.email);
                console.log('Is admin check:', firebaseUser.email === 'admin@example.com');
                
                const userData = {
                    user_id: firebaseUser.uid,
                    username: firebaseUser.email.split('@')[0],
                    firstname: firebaseUser.email === 'admin@example.com' ? 'Admin' : 'User',
                    lastname: firebaseUser.email === 'admin@example.com' ? 'User' : '',
                    email: firebaseUser.email,
                    role: firebaseUser.email === 'admin@example.com' ? 'admin' : 'user',
                    firebase_uid: firebaseUser.uid
                };
                console.log('FIREBASE Login successful, user data:', userData);
                setUser(userData);
                return { success: true, user: userData };
            }
            return { success: false, message: 'Login failed' };
        } catch (error) {
            console.error('Error during login:', error);
            setError('Login failed');
            return { success: false, message: 'Login failed' };
        } finally {
            setLoading(false);
        }
    };

    // Logout function
    const logout = async () => {
        setLoading(true);
        try {
            // Sign out from Firebase
            await signOut(auth);
            
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