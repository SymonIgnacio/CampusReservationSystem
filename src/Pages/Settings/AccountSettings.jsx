import React, { useState, useEffect, useContext } from 'react';
import { AuthContext } from '../../context/AuthContext';
import './AccountSettings.css';

// Base API URL
const API_BASE_URL = 'http://localhost/CampusReservationSystem/src/api';

const AccountSettings = () => {
  const { user, updateUser } = useContext(AuthContext);
  const [formData, setFormData] = useState({
    firstname: '',
    lastname: '',
    email: '',
    phone: '',
    department: '',
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);
  const [activeTab, setActiveTab] = useState('profile');

  useEffect(() => {
    if (user) {
      // Populate form with user data
      setFormData(prev => ({
        ...prev,
        firstname: user.firstname || '',
        lastname: user.lastname || '',
        email: user.email || '',
        phone: user.phone || '',
        department: user.department || ''
      }));
    }
  }, [user]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleProfileSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    setSuccess(null);

    try {
      const response = await fetch(`${API_BASE_URL}/update_user.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          user_id: user.user_id,
          firstname: formData.firstname,
          lastname: formData.lastname,
          username: user.username,
          email: formData.email,
          phone: formData.phone,
          department: formData.department
        }),
        mode: 'cors'
      });

      const result = await response.json();
      
      if (result.success) {
        setSuccess('Profile updated successfully!');
      } else {
        throw new Error(result.message || 'Failed to update profile');
      }
      
      // Update user context if available
      if (updateUser) {
        updateUser({
          ...user,
          firstname: formData.firstname,
          lastname: formData.lastname,
          email: formData.email,
          phone: formData.phone,
          department: formData.department
        });
      }
    } catch (err) {
      console.error('Error updating profile:', err);
      setError(`Failed to update profile: ${err.message}`);
    } finally {
      setLoading(false);
    }
  };

  const handlePasswordSubmit = async (e) => {
    e.preventDefault();
    
    // Validate passwords
    if (formData.newPassword !== formData.confirmPassword) {
      setError('New passwords do not match');
      return;
    }
    
    setLoading(true);
    setError(null);
    setSuccess(null);

    try {
      // If user has Firebase UID, update password in Firebase
      if (user.firebase_uid) {
        const { updatePassword, reauthenticateWithCredential, EmailAuthProvider } = await import('firebase/auth');
        const { auth } = await import('../../firebase');
        
        // Re-authenticate user first
        const credential = EmailAuthProvider.credential(user.email, formData.currentPassword);
        await reauthenticateWithCredential(auth.currentUser, credential);
        
        // Update password in Firebase
        await updatePassword(auth.currentUser, formData.newPassword);
        
        setSuccess('Password updated successfully!');
      } else {
        // For local users, use the API
        const response = await fetch(`${API_BASE_URL}/update_password.php`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            user_id: user.user_id,
            current_password: formData.currentPassword,
            new_password: formData.newPassword
          }),
          mode: 'cors'
        });

        const result = await response.json();
        
        if (result.success) {
          setSuccess('Password updated successfully!');
        } else {
          throw new Error(result.message || 'Failed to update password');
        }
      }
      
      // Clear password fields
      setFormData(prev => ({
        ...prev,
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      }));
    } catch (err) {
      console.error('Error updating password:', err);
      if (err.code === 'auth/wrong-password') {
        setError('Current password is incorrect');
      } else if (err.code === 'auth/weak-password') {
        setError('New password is too weak');
      } else {
        setError(`Failed to update password: ${err.message}`);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="account-settings-container">
      <h1>Account Settings</h1>
      
      <div className="settings-tabs">
        <button 
          className={`tab-btn ${activeTab === 'profile' ? 'active' : ''}`}
          onClick={() => setActiveTab('profile')}
        >
          Profile Information
        </button>
        <button 
          className={`tab-btn ${activeTab === 'password' ? 'active' : ''}`}
          onClick={() => setActiveTab('password')}
        >
          Change Password
        </button>
      </div>

      {error && <div className="error-message">{error}</div>}
      {success && <div className="success-message">{success}</div>}

      {activeTab === 'profile' && (
        <div className="settings-form">
          <form onSubmit={handleProfileSubmit}>
            <div className="form-row">
              <div className="form-group">
                <label htmlFor="firstname">First Name</label>
                <input
                  type="text"
                  id="firstname"
                  name="firstname"
                  value={formData.firstname}
                  onChange={handleChange}
                  required
                />
              </div>
              
              <div className="form-group">
                <label htmlFor="lastname">Last Name</label>
                <input
                  type="text"
                  id="lastname"
                  name="lastname"
                  value={formData.lastname}
                  onChange={handleChange}
                  required
                />
              </div>
            </div>

            <div className="form-group">
              <label htmlFor="email">Email</label>
              <input
                type="email"
                id="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                required
              />
            </div>

            <div className="form-group">
              <label htmlFor="phone">Phone Number</label>
              <input
                type="tel"
                id="phone"
                name="phone"
                value={formData.phone}
                onChange={handleChange}
              />
            </div>

            <div className="form-group">
              <label htmlFor="department">Department</label>
              <select
                id="department"
                name="department"
                value={formData.department}
                onChange={handleChange}
              >
                <option value="">Select Department</option>
                <option value="College of Computer Studies">College of Computer Studies</option>
                <option value="College of Accountancy">College of Accountancy</option>
                <option value="College of Arts And Science">College of Arts And Science</option>
                <option value="College Of Education">College Of Education</option>
                <option value="College of Hospitality Management and Tourism">College of Hospitality Management and Tourism</option>
                <option value="College Of Business Administration">College Of Business Administration</option>
                <option value="College of Health and Sciences">College of Health and Sciences</option>
                <option value="School of Psychology">School of Psychology</option>
                <option value="College of Maritime Education">College of Maritime Education</option>
                <option value="School of Mechanical Engineering">School of Mechanical Engineering</option>
              </select>
            </div>

            <div className="form-actions">
              <button 
                type="submit" 
                className="submit-btn"
                disabled={loading}
              >
                {loading ? 'Updating...' : 'Update Profile'}
              </button>
            </div>
          </form>
        </div>
      )}

      {activeTab === 'password' && (
        <div className="settings-form">
          <form onSubmit={handlePasswordSubmit}>
            <div className="form-group">
              <label htmlFor="currentPassword">Current Password</label>
              <input
                type="password"
                id="currentPassword"
                name="currentPassword"
                value={formData.currentPassword}
                onChange={handleChange}
                required
              />
            </div>

            <div className="form-group">
              <label htmlFor="newPassword">New Password</label>
              <input
                type="password"
                id="newPassword"
                name="newPassword"
                value={formData.newPassword}
                onChange={handleChange}
                required
                minLength="6"
              />
            </div>

            <div className="form-group">
              <label htmlFor="confirmPassword">Confirm New Password</label>
              <input
                type="password"
                id="confirmPassword"
                name="confirmPassword"
                value={formData.confirmPassword}
                onChange={handleChange}
                required
                minLength="6"
              />
            </div>

            <div className="form-actions">
              <button 
                type="submit" 
                className="submit-btn"
                disabled={loading}
              >
                {loading ? 'Updating...' : 'Change Password'}
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
};

export default AccountSettings;