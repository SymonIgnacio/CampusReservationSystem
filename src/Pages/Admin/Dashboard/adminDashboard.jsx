import React, { useContext, useState, useEffect } from 'react';
import { EventContext } from '../../../context/EventContext';
import { AuthContext } from '../../../context/AuthContext';
import 'boxicons/css/boxicons.min.css';
import './adminDashboard.css';

// Helper function to render icons with fallback
const Icon = ({ iconClass }) => {
  const [iconsLoaded, setIconsLoaded] = useState(true);
  
  useEffect(() => {
    // Check if Boxicons is loaded
    const testIcon = document.createElement('i');
    testIcon.className = 'bx bx-menu';
    document.body.appendChild(testIcon);
    
    const computedStyle = window.getComputedStyle(testIcon);
    const isLoaded = computedStyle.fontFamily.includes('boxicons') || 
                    computedStyle.fontFamily.includes('BoxIcons');
    
    document.body.removeChild(testIcon);
    setIconsLoaded(isLoaded);
  }, []);
  
  if (iconsLoaded) {
    return <i className={`bx ${iconClass}`}></i>;
  } else {
    // Map to Font Awesome icons as fallback
    const iconMap = {
      'bx-refresh': 'fa-solid fa-arrows-rotate',
      'bx-filter': 'fa-solid fa-filter'
    };
    return <i className={iconMap[iconClass] || 'fa-solid fa-circle'}></i>;
  }
};

function AdminDashboard({ isCollapsed }) {
  const { events, stats, loading, error, refreshData } = useContext(EventContext);
  const { user } = useContext(AuthContext);
  const [filter, setFilter] = useState('all'); // Show all events by default

  // Refresh data when component mounts
  useEffect(() => {
    refreshData();
  }, []);

  // Get events based on filter
  const getFilteredEvents = () => {
    if (!events || events.length === 0) {
      return [];
    }
    
    // Filter events based on selected filter
    if (filter === 'all') {
      return events;
    } else {
      return events.filter(event => event.status === filter);
    }
  };

  const filteredEvents = getFilteredEvents();

  // Format date to be more readable
  const formatDate = (dateString) => {
    try {
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', options);
    } catch (error) {
      return dateString; // Return original string if parsing fails
    }
  };

  // Helper function to get field value with fallbacks
  const getFieldValue = (event, fieldNames) => {
    for (const fieldName of fieldNames) {
      if (event[fieldName] !== undefined) {
        return event[fieldName];
      }
    }
    return 'N/A';
  };

  // Handle filter change
  const handleFilterChange = (newFilter) => {
    setFilter(newFilter);
  };

  return (
    <div className={`dashboard-container ${isCollapsed ? 'collapsed' : ''}`}>
      {/* Main Content */}
      <main className="main-content">
        <div className="dashboard-header">
          <h1 className="page-title">ADMIN DASHBOARD</h1>
        </div>

        {/* Stats Cards */}
        <div className="stats-cards">
          <div className="card yellow" onClick={() => handleFilterChange('all')}>
            <h2>{stats.total}</h2>
            <p>RESERVATIONS</p>
          </div>
          <div className="card blue" onClick={() => handleFilterChange('pending')}>
            <h2>{stats.pending}</h2>
            <p>PENDING</p>
          </div>
          <div className="card red" onClick={() => handleFilterChange('declined')}>
            <h2>{stats.declined}</h2>
            <p>DECLINED</p>
          </div>
          <div className="card green" onClick={() => handleFilterChange('approved')}>
            <h2>{stats.approved}</h2>
            <p>APPROVED</p>
          </div>
        </div>

        {/* All Reservations */}
        <div className="upcoming-events">
          <div className="events-header">
            <h2>
              {filter === 'all' ? 'ALL RESERVATIONS' : 
               filter === 'pending' ? 'PENDING RESERVATIONS' :
               filter === 'declined' ? 'DECLINED RESERVATIONS' : 'APPROVED RESERVATIONS'}
            </h2>
            <div className="filter-controls">
              <div className="filter-buttons">
                <button 
                  className={`filter-button ${filter === 'all' ? 'active' : ''}`}
                  onClick={() => handleFilterChange('all')}
                >
                  All
                </button>
                <button 
                  className={`filter-button ${filter === 'pending' ? 'active' : ''}`}
                  onClick={() => handleFilterChange('pending')}
                >
                  Pending
                </button>
                <button 
                  className={`filter-button ${filter === 'approved' ? 'active' : ''}`}
                  onClick={() => handleFilterChange('approved')}
                >
                  Approved
                </button>
                <button 
                  className={`filter-button ${filter === 'declined' ? 'active' : ''}`}
                  onClick={() => handleFilterChange('declined')}
                >
                  Declined
                </button>
              </div>
              <button 
                className="refresh-button" 
                onClick={refreshData}
                disabled={loading}
              >
                {loading ? 'Loading...' : (
                  <>
                    <Icon iconClass="bx-refresh" /> Refresh
                  </>
                )}
              </button>
            </div>
          </div>

          {error && (
            <div className="error-message">
              <p>{error}</p>
            </div>
          )}

          {loading ? (
            <p className="loading-message">Loading reservations...</p>
          ) : filteredEvents.length > 0 ? (
            <div className="table-container">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>RESERVATION</th>
                    <th>DATE</th>
                    <th>TIME</th>
                    <th>LOCATION</th>
                    <th>STATUS</th>
                    <th>RESERVED BY</th>
                    <th>ACTIONS</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredEvents.map(event => (
                    <tr key={getFieldValue(event, ['id', 'reservation_id'])} className={`status-${event.status}`}>
                      <td>{getFieldValue(event, ['id', 'reservation_id'])}</td>
                      <td>{getFieldValue(event, ['name', 'title', 'activity', 'reservation_name', 'event_name', 'description'])}</td>
                      <td>{formatDate(getFieldValue(event, ['date', 'date_from', 'reservation_date', 'event_date']))}</td>
                      <td>{getFieldValue(event, ['time', 'time_start', 'reservation_time', 'event_time'])}</td>
                      <td>{getFieldValue(event, ['place', 'location', 'venue', 'resource_name'])}</td>
                      <td>
                        <span className={`status-badge ${event.status || 'pending'}`}>
                          {(event.status || 'pending').toUpperCase()}
                        </span>
                      </td>
                      <td>{getFieldValue(event, ['organizer', 'requestor_name', 'reserved_by', 'user_id'])}</td>
                      <td>
                        {event.status === 'pending' ? (
                          <div className="action-buttons">
                            <button className="approve-btn">Approve</button>
                            <button className="decline-btn">Decline</button>
                          </div>
                        ) : (
                          <span className={`action-taken ${event.status}`}>
                            {event.status === 'approved' ? 'Approved' : 'Declined'}
                          </span>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <p className="no-events">No {filter !== 'all' ? filter : ''} reservations found.</p>
          )}
        </div>
      </main>
    </div>
  );
}

export default AdminDashboard;