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
      case 'departments':
        return <div className="placeholder">Department & Venue Setup - Coming Soon</div>;
      case 'inventory':
        return <div className="placeholder">Inventory Control - Coming Soon</div>;
      case 'audit':
        return <div className="placeholder">Audit Trail / Logs - Coming Soon</div>;
      case 'settings':
        return <div className="placeholder">System Settings - Coming Soon</div>;
      case 'reports':
        return <div className="placeholder">Report Generator - Coming Soon</div>;
      case 'feedback':
        return <div className="placeholder">Feedback & Issues - Coming Soon</div>;
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
        <button 
          className={`tab ${activeTab === 'departments' ? 'active' : ''}`}
          onClick={() => setActiveTab('departments')}
        >
          Dept & Venues
        </button>
        <button 
          className={`tab ${activeTab === 'inventory' ? 'active' : ''}`}
          onClick={() => setActiveTab('inventory')}
        >
          Inventory
        </button>
        <button 
          className={`tab ${activeTab === 'audit' ? 'active' : ''}`}
          onClick={() => setActiveTab('audit')}
        >
          Audit Logs
        </button>
        <button 
          className={`tab ${activeTab === 'settings' ? 'active' : ''}`}
          onClick={() => setActiveTab('settings')}
        >
          Settings
        </button>
        <button 
          className={`tab ${activeTab === 'reports' ? 'active' : ''}`}
          onClick={() => setActiveTab('reports')}
        >
          Reports
        </button>
        <button 
          className={`tab ${activeTab === 'feedback' ? 'active' : ''}`}
          onClick={() => setActiveTab('feedback')}
        >
          Feedback
        </button>
      </div>
      
      <div className="tab-content">
        {renderTabContent()}
      </div>
    </div>
  );
};

export default SysAdmin;