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
            const skipVerificationEmails = ['admin@example.com', 'systemadmin@example.com', 'VPO@example.com', 'vpo@example.com', 'Vpo@example.com'];
            if (firebaseUser && (firebaseUser.emailVerified || skipVerificationEmails.includes(firebaseUser.email))) {
                // Fetch user data from database
                try {
                    const response = await fetch('http://localhost/CampusReservationSystem/src/api/get_user_by_firebase_uid.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ firebase_uid: firebaseUser.uid })
                    });
                    const dbData = await response.json();
                    
                    if (dbData.success && dbData.user) {
                        const userData = {
                            user_id: dbData.user.user_id,
                            username: dbData.user.username,
                            firstname: dbData.user.firstname,
                            lastname: dbData.user.lastname,
                            email: dbData.user.email,
                            department: dbData.user.department,
                            role: dbData.user.role,
                            firebase_uid: firebaseUser.uid
                        };
                        setUser(userData);
                        return;
                    }
                } catch (error) {
                    console.error('Error fetching user data:', error);
                }
                
                // Fallback for special accounts
                const userData = {
                    user_id: firebaseUser.uid,
                    username: firebaseUser.email.split('@')[0],
                    firstname: firebaseUser.email === 'admin@example.com' ? 'Admin' : 
                              firebaseUser.email === 'systemadmin@example.com' ? 'System' : 
                              (firebaseUser.email === 'VPO@example.com' || firebaseUser.email === 'Vpo@example.com' || firebaseUser.email === 'vpo@example.com') ? 'Vice President' : 'User',
                    lastname: firebaseUser.email === 'admin@example.com' ? 'User' : 
                             firebaseUser.email === 'systemadmin@example.com' ? 'Administrator' : 
                             (firebaseUser.email === 'VPO@example.com' || firebaseUser.email === 'Vpo@example.com' || firebaseUser.email === 'vpo@example.com') ? 'Office' : '',
                    email: firebaseUser.email,
                    role: firebaseUser.email === 'admin@example.com' ? 'admin' : 
                          firebaseUser.email === 'systemadmin@example.com' ? 'sysadmin' : 
                          (firebaseUser.email === 'VPO@example.com' || firebaseUser.email === 'Vpo@example.com' || firebaseUser.email === 'vpo@example.com') ? 'vpo' : 'user',
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
                
                // Fetch user data from database
                try {
                    const response = await fetch('http://localhost/CampusReservationSystem/src/api/get_user_by_firebase_uid.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ firebase_uid: firebaseUser.uid })
                    });
                    const dbData = await response.json();
                    
                    if (dbData.success && dbData.user) {
                        const userData = {
                            user_id: dbData.user.user_id,
                            username: dbData.user.username,
                            firstname: dbData.user.firstname,
                            lastname: dbData.user.lastname,
                            email: dbData.user.email,
                            department: dbData.user.department,
                            role: dbData.user.role,
                            firebase_uid: firebaseUser.uid
                        };
                        console.log('FIREBASE Login successful, user data:', userData);
                        setUser(userData);
                        return { success: true, user: userData };
                    }
                } catch (error) {
                    console.error('Error fetching user data:', error);
                }
                
                // Fallback for special accounts
                const userData = {
                    user_id: firebaseUser.uid,
                    username: firebaseUser.email.split('@')[0],
                    firstname: firebaseUser.email === 'admin@example.com' ? 'Admin' : 
                              firebaseUser.email === 'systemadmin@example.com' ? 'System' : 
                              (firebaseUser.email === 'VPO@example.com' || firebaseUser.email === 'Vpo@example.com' || firebaseUser.email === 'vpo@example.com') ? 'Vice President' : 'User',
                    lastname: firebaseUser.email === 'admin@example.com' ? 'User' : 
                             firebaseUser.email === 'systemadmin@example.com' ? 'Administrator' : 
                             (firebaseUser.email === 'VPO@example.com' || firebaseUser.email === 'Vpo@example.com' || firebaseUser.email === 'vpo@example.com') ? 'Office' : '',
                    email: firebaseUser.email,
                    role: firebaseUser.email === 'admin@example.com' ? 'admin' : 
                          firebaseUser.email === 'systemadmin@example.com' ? 'sysadmin' : 
                          (firebaseUser.email === 'VPO@example.com' || firebaseUser.email === 'Vpo@example.com' || firebaseUser.email === 'vpo@example.com') ? 'vpo' : 'user',
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