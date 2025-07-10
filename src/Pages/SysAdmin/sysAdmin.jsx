import React, { useState, useContext } from 'react';
import { useNavigate } from 'react-router-dom';
import { AuthContext } from '../../context/AuthContext';
import SysAdminDashboard from './Dashboard/sysAdminDashboard';
import UserManagement from './UserManagement/userManagement';
import './sysAdmin.css';

const SysAdmin = () => {
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
        return <SysAdminDashboard />;
      case 'users':
        return <UserManagement />;
      default:
        return <SysAdminDashboard />;
    }
  };

  return (
    <div className="sysadmin-container">
      <div className="sysadmin-header">
        <h1 className="sysadmin-title">SYSTEM ADMINISTRATION</h1>
        <button className="logout-btn" onClick={handleLogout}>
          Logout
        </button>
      </div>
      
      <div className="sysadmin-tabs">
        <button 
          className={`tab ${activeTab === 'dashboard' ? 'active' : ''}`}
          onClick={() => setActiveTab('dashboard')}
        >
          Dashboard
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

export default SysAdmin;