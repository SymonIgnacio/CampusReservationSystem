import React, { useState, useContext } from 'react';
import { useNavigate } from 'react-router-dom';
import { AuthContext } from '../../context/AuthContext';
import VPODashboard from './Dashboard/vpoDashboard';
import VPORequests from './Requests/vpoRequests';
import UserManagement from '../SysAdmin/UserManagement/userManagement';
import './vpo.css';

const VPO = () => {
  const [activeTab, setActiveTab] = useState('dashboard');
  const { logout } = useContext(AuthContext);
  const navigate = useNavigate();
  
  const handleLogout = async () => {
    if (window.confirm('Are you sure you want to logout?')) {
      await logout();
      navigate('/');
    }
  };

  const renderTabContent = () => {
    switch (activeTab) {
      case 'dashboard':
        return <VPODashboard />;
      case 'requests':
        return <VPORequests />;
      case 'users':
        return <UserManagement />;
      default:
        return <VPODashboard />;
    }
  };

  return (
    <div className="vpo-container">
      <div className="vpo-header">
        <h1 className="vpo-title">VICE PRESIDENT'S OFFICE</h1>
        <button className="logout-btn" onClick={handleLogout}>
          Logout
        </button>
      </div>
      
      <div className="vpo-tabs">
        <button 
          className={`tab ${activeTab === 'dashboard' ? 'active' : ''}`}
          onClick={() => setActiveTab('dashboard')}
        >
          Dashboard
        </button>
        <button 
          className={`tab ${activeTab === 'requests' ? 'active' : ''}`}
          onClick={() => setActiveTab('requests')}
        >
          Requests
        </button>
        <button 
          className={`tab ${activeTab === 'users' ? 'active' : ''}`}
          onClick={() => setActiveTab('users')}
        >
          User Management
        </button>
      </div>
      
      <div className="tab-content">
        {renderTabContent()}
      </div>
    </div>
  );
};

export default VPO;