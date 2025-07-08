// Run this in browser console on your React app to create sysadmin account
import { createUserWithEmailAndPassword } from 'firebase/auth';
import { auth } from './src/firebase';

// Create sysadmin account
createUserWithEmailAndPassword(auth, 'systemadmin@example.com', '123456')
  .then((userCredential) => {
    console.log('SysAdmin account created:', userCredential.user.email);
    // No email verification needed - handled in AuthContext
  })
  .catch((error) => {
    console.error('Error creating account:', error.message);
  });