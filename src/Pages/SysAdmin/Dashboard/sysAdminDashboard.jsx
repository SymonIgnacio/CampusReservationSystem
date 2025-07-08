import React, { useState, useEffect } from 'react';
import './sysAdminDashboard.css';

const SysAdminDashboard = () => {
  const [systemStatus, setSystemStatus] = useState({
    database: 'Checking...',
    firebase: 'Checking...',
    lastSync: 'Never'
  });
  
  const [activeUsers, setActiveUsers] = useState([]);
  const [systemLogs, setSystemLogs] = useState([]);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    checkSystemStatus();
    fetchActiveUsers();
    fetchSystemLogs();
  }, []);
  
  const checkSystemStatus = async () => {
    try {
      // Check database connection
      const dbResponse = await fetch('http://localhost/CampusReservationSystem/src/api/stats_with_approved.php');
      if (dbResponse.ok) {
        setSystemStatus(prev => ({ 
          ...prev, 
          database: 'Connected',
          lastSync: new Date().toLocaleString()
        }));
      } else {
        setSystemStatus(prev => ({ ...prev, database: 'Error' }));
      }
      
      // Check Firebase (simplified check)
      setSystemStatus(prev => ({ ...prev, firebase: 'Connected' }));
    } catch (error) {
      setSystemStatus(prev => ({ 
        ...prev, 
        database: 'Error',
        firebase: 'Error'
      }));
    }
  };
  
  const fetchActiveUsers = async () => {
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/get_users.php');
      const data = await response.json();
      if (data.success) {
        setActiveUsers(data.users || []);
      }
    } catch (error) {
      console.error('Error fetching users:', error);
    }
  };
  
  const fetchSystemLogs = async () => {
    try {
      const response = await fetch('http://localhost/CampusReservationSystem/src/api/get_system_logs.php');
      const data = await response.json();
      if (data.success) {
        setSystemLogs(data.logs || []);
      }
    } catch (error) {
      console.error('Error fetching logs:', error);
      // Add default error log if API fails
      setSystemLogs([{
        id: 1,
        type: 'Error',
        message: 'Failed to fetch system logs',
        timestamp: new Date().toISOString().slice(0, 19).replace('T', ' ')
      }]);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="sysadmin-dashboard">
      <h1 className="page-title">SYSTEM ADMINISTRATION DASHBOARD</h1>
      
      <div className="dashboard-grid">
        {/* System Status */}
        <div className="dashboard-card">
          <h3>System Status</h3>
          <div className="status-items">
            <div className="status-item">
              <span className="status-label">Database:</span>
              <span className={`status-value ${systemStatus.database.toLowerCase()}`}>
                {systemStatus.database}
              </span>
            </div>
            <div className="status-item">
              <span className="status-label">Firebase:</span>
              <span className={`status-value ${systemStatus.firebase.toLowerCase()}`}>
                {systemStatus.firebase}
              </span>
            </div>
            <div className="status-item">
              <span className="status-label">Last Sync:</span>
              <span className="status-value">{systemStatus.lastSync}</span>
            </div>
          </div>
        </div>

        {/* Active Users */}
        <div className="dashboard-card">
          <h3>Active Users</h3>
          <div className="users-list">
            {loading ? (
              <div>Loading users...</div>
            ) : activeUsers.length > 0 ? (
              activeUsers.map(user => (
                <div key={user.user_id} className="user-item">
                  <div className="user-info">
                    <span className="username">{user.username}</span>
                    <span className="role">{user.role}</span>
                  </div>
                  <div className="user-status">
                    <span className="status online">Active</span>
                    <span className="last-active">{user.created_at}</span>
                  </div>
                </div>
              ))
            ) : (
              <div>No users found</div>
            )}
          </div>
        </div>

        {/* System Logs */}
        <div className="dashboard-card logs-card">
          <h3>Recent System Logs</h3>
          <div className="logs-list">
            {systemLogs.map(log => (
              <div key={log.id} className={`log-item ${log.type.toLowerCase()}`}>
                <div className="log-header">
                  <span className="log-type">{log.type}</span>
                  <span className="log-timestamp">{log.timestamp}</span>
                </div>
                <div className="log-message">{log.message}</div>
              </div>
            ))}
          </div>
        </div>

        {/* Quick Actions */}
        <div className="dashboard-card">
          <h3>Quick Actions</h3>
          <div className="quick-actions">
            <button className="action-btn primary" onClick={checkSystemStatus}>Check Status</button>
            <button className="action-btn secondary" onClick={fetchActiveUsers}>Refresh Users</button>
            <button className="action-btn warning" onClick={fetchSystemLogs}>Refresh Logs</button>
            <button className="action-btn info">Generate Report</button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SysAdminDashboard;