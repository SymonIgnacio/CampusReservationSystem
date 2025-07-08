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
      'bx-filter': 'fa-solid fa-filter',
      'bx-search': 'fa-solid fa-search',
      'bx-x': 'fa-solid fa-times'
    };
    return <i className={iconMap[iconClass] || 'fa-solid fa-circle'}></i>;
  }
};

function AdminDashboard({ isCollapsed }) {
  const { loading, error } = useContext(EventContext);
  const { user } = useContext(AuthContext);
  const [events, setEvents] = useState([]);
  const [eventsByDate, setEventsByDate] = useState({});
  const [searchTerm, setSearchTerm] = useState('');
  const [sortBy, setSortBy] = useState('date');
  const [sortOrder, setSortOrder] = useState('asc');
  const [selectedMonth, setSelectedMonth] = useState('');
  const [stats, setStats] = useState({
    total: 0,
    pending: 0,
    approved: 0,
    declined: 0
  });
  const [pendingCount, setPendingCount] = useState(0);
  const [declinedCount, setDeclinedCount] = useState(0);
  const [finishedCount, setFinishedCount] = useState(0);

  // Fetch approved events and stats when component mounts
  useEffect(() => {
    const fetchApprovedEvents = async () => {
      try {
        const response = await fetch("http://localhost/CampusReservationSystem/src/api/admin_dashboard_approved_events.php", {
          credentials: 'include',
          mode: 'cors'
        });
        const data = await response.json();
        if (data.success) {
          // Set events directly from the API response
          setEvents(data.events || []);
          // Set events grouped by date
          setEventsByDate(data.eventsByDate || {});
        }
      } catch (error) {
        console.error("Error fetching approved events:", error);
      }
    };
    
    const fetchStats = async () => {
      try {
        const response = await fetch("http://localhost/CampusReservationSystem/src/api/stats_with_approved.php", {
          credentials: 'include',
          mode: 'cors'
        });
        const data = await response.json();
        if (data.success || data.status === 'success') {
          // Handle both response formats
          const statsData = data.stats || {
            approved: data.approved_events || 0,
            pending: data.pending_events || 0,
            declined: data.declined_events || 0,
            total: data.total_events || 0
          };
          setStats(statsData);
        }
      } catch (error) {
        console.error("Error fetching stats:", error);
      }
    };
    
    const fetchPendingCount = async () => {
      try {
        const response = await fetch("http://localhost/CampusReservationSystem/src/api/get_pending_count.php", {
          credentials: 'include',
          mode: 'cors'
        });
        const data = await response.json();
        if (data.success) {
          setPendingCount(data.pending_count);
        }
      } catch (error) {
        console.error("Error fetching pending count:", error);
      }
    };
    
    const fetchDeclinedCount = async () => {
      try {
        const response = await fetch("http://localhost/CampusReservationSystem/src/api/get_declined_count.php", {
          credentials: 'include',
          mode: 'cors'
        });
        const data = await response.json();
        if (data.success) {
          setDeclinedCount(data.declined_count);
        }
      } catch (error) {
        console.error("Error fetching declined count:", error);
      }
    };
    
    const fetchFinishedCount = async () => {
      try {
        const response = await fetch("http://localhost/CampusReservationSystem/src/api/get_finished_count.php", {
          credentials: 'include',
          mode: 'cors'
        });
        const data = await response.json();
        if (data.success) {
          setFinishedCount(data.finished_count);
        }
      } catch (error) {
        console.error("Error fetching finished count:", error);
      }
    };
    
    fetchApprovedEvents();
    fetchStats();
    fetchPendingCount();
    fetchDeclinedCount();
    fetchFinishedCount();
  }, []);

  // Helper function to get field value with fallbacks
  const getFieldValue = (event, fieldNames) => {
    for (const fieldName of fieldNames) {
      if (event[fieldName] !== undefined) {
        return event[fieldName];
      }
    }
    return 'N/A';
  };

  // Format date to "Month DD, YYYY" format
  const formatDate = (dateString) => {
    try {
      const date = new Date(dateString);
      const month = date.toLocaleString('en-US', { month: 'long' });
      const day = date.getDate().toString().padStart(2, '0');
      const year = date.getFullYear();
      return `${month} ${day}, ${year}`;
    } catch (error) {
      return dateString; // Return original string if parsing fails
    }
  };

  // Filter events based on search term and month
  const searchedEvents = events.filter(event => {
    // Search filter
    if (searchTerm !== '') {
      const eventName = getFieldValue(event, ['activity', 'name', 'title', 'event_name']).toLowerCase();
      const eventDate = getFieldValue(event, ['date', 'date_need_from']).toLowerCase();
      const eventTime = getFieldValue(event, ['time', 'start_time']).toLowerCase();
      const eventLocation = getFieldValue(event, ['venue', 'location', 'place']).toLowerCase();
      
      const search = searchTerm.toLowerCase();
      if (!(eventName.includes(search) || eventDate.includes(search) || eventTime.includes(search) || eventLocation.includes(search))) {
        return false;
      }
    }
    
    // Month filter
    if (selectedMonth !== '') {
      const eventDate = getFieldValue(event, ['date', 'date_need_from']);
      if (eventDate && eventDate !== 'N/A') {
        const eventMonth = new Date(eventDate).getMonth() + 1;
        if (eventMonth.toString() !== selectedMonth) {
          return false;
        }
      }
    }
    
    return true;
  });

  // Sort events based on current sort criteria
  const filteredEvents = [...searchedEvents].sort((a, b) => {
    let valueA, valueB;
    
    switch(sortBy) {
      case 'name':
      case 'event':
        valueA = getFieldValue(a, ['activity', 'name', 'title', 'event_name']).toLowerCase();
        valueB = getFieldValue(b, ['activity', 'name', 'title', 'event_name']).toLowerCase();
        break;
      case 'date':
        valueA = new Date(getFieldValue(a, ['date', 'date_need_from']));
        valueB = new Date(getFieldValue(b, ['date', 'date_need_from']));
        break;
      case 'time':
        valueA = getFieldValue(a, ['time', 'start_time']).toLowerCase();
        valueB = getFieldValue(b, ['time', 'start_time']).toLowerCase();
        break;
      case 'location':
        valueA = getFieldValue(a, ['venue', 'location', 'place']).toLowerCase();
        valueB = getFieldValue(b, ['venue', 'location', 'place']).toLowerCase();
        break;
      default:
        valueA = new Date(getFieldValue(a, ['date', 'date_need_from']));
        valueB = new Date(getFieldValue(b, ['date', 'date_need_from']));
    }
    
    return sortOrder === 'asc' ? (valueA > valueB ? 1 : -1) : (valueA < valueB ? 1 : -1);
  });

  return (
    <div className={`admin-dashboard-container`}>
      <main className="main-content">
        <div className="dashboard-header">
          <h1 className="admin-dashboard-page-title">ADMIN DASHBOARD</h1>
        </div>

        {/* Stats Cards */}
        <div className="stats-cards">
          <div className="card yellow">
            <h2>{events.length}</h2>
            <p>UPCOMING EVENTS</p>
          </div>
          <div className="card blue">
            <h2>{pendingCount}</h2>
            <p>PENDING</p>
          </div>
          <div className="card red">
            <h2>{declinedCount}</h2>
            <p>DECLINED</p>
          </div>
          <div className="card green">
            <h2>{stats.approved || 0}</h2>
            <p>APPROVED</p>
          </div>
          <div className="card purple">
            <h2>{finishedCount}</h2>
            <p>FINISHED EVENTS</p>
          </div>
        </div>

        {/* All Reservations */}
        <div className="upcoming-events">
          <h2>UPCOMING EVENTS</h2>
          <div className="events-header">
            
            <div className="admin-dashboard-search-container">
              <div className="search-box">
                <Icon iconClass="bx-search" />
                <input
                  className="admin-dashboard-search-input"
                  type="text"
                  placeholder="Search events"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                />
              </div>
            </div>
            <div className="filter-controls">
              <select 
                value={selectedMonth} 
                onChange={(e) => setSelectedMonth(e.target.value)}
                className="month-filter"
              >
                <option value="">All Months</option>
                <option value="1">January</option>
                <option value="2">February</option>
                <option value="3">March</option>
                <option value="4">April</option>
                <option value="5">May</option>
                <option value="6">June</option>
                <option value="7">July</option>
                <option value="8">August</option>
                <option value="9">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
              </select>
              <button 
                className="refresh-button" 
                onClick={() => {
                  // Refresh approved events and stats
                  const fetchApprovedEvents = async () => {
                    try {
                      const response = await fetch("http://localhost/CampusReservationSystem/src/api/admin_dashboard_approved_events.php", {
                        credentials: 'include',
                        mode: 'cors'
                      });
                      const data = await response.json();
                      if (data.success) {
                        setEvents(data.events || []);
                        setEventsByDate(data.eventsByDate || {});
                      }
                    } catch (error) {
                      console.error("Error fetching approved events:", error);
                    }
                  };
                  
                  const fetchStats = async () => {
                    try {
                      const response = await fetch("http://localhost/CampusReservationSystem/src/api/stats_with_approved.php", {
                        credentials: 'include',
                        mode: 'cors'
                      });
                      const data = await response.json();
                      if (data.success || data.status === 'success') {
                        const statsData = data.stats || {
                          approved: data.approved_events || 0,
                          pending: data.pending_events || 0,
                          declined: data.declined_events || 0,
                          total: data.total_events || 0
                        };
                        setStats(statsData);
                      }
                    } catch (error) {
                      console.error("Error fetching stats:", error);
                    }
                  };
                  
                  const fetchPendingCount = async () => {
                    try {
                      const response = await fetch("http://localhost/CampusReservationSystem/src/api/get_pending_count.php", {
                        credentials: 'include',
                        mode: 'cors'
                      });
                      const data = await response.json();
                      if (data.success) {
                        setPendingCount(data.pending_count);
                      }
                    } catch (error) {
                      console.error("Error fetching pending count:", error);
                    }
                  };
                  
                  const fetchDeclinedCount = async () => {
                    try {
                      const response = await fetch("http://localhost/CampusReservationSystem/src/api/get_declined_count.php", {
                        credentials: 'include',
                        mode: 'cors'
                      });
                      const data = await response.json();
                      if (data.success) {
                        setDeclinedCount(data.declined_count);
                      }
                    } catch (error) {
                      console.error("Error fetching declined count:", error);
                    }
                  };
                  
                  const fetchFinishedCount = async () => {
                    try {
                      const response = await fetch("http://localhost/CampusReservationSystem/src/api/get_finished_count.php", {
                        credentials: 'include',
                        mode: 'cors'
                      });
                      const data = await response.json();
                      if (data.success) {
                        setFinishedCount(data.finished_count);
                      }
                    } catch (error) {
                      console.error("Error fetching finished count:", error);
                    }
                  };
                  
                  fetchApprovedEvents();
                  fetchStats();
                  fetchPendingCount();
                  fetchDeclinedCount();
                  fetchFinishedCount();
                }}
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
            <p className="loading-message">Loading events...</p>
          ) : filteredEvents.length > 0 ? (
            <div className="table-container">
              <table>
                <thead>
                  <tr>
                    <th onClick={() => {
                      setSortBy('event');
                      setSortOrder(sortBy === 'event' ? (sortOrder === 'asc' ? 'desc' : 'asc') : 'asc');
                    }} className="sortable-header">
                      EVENT {sortBy === 'event' && (sortOrder === 'asc' ? '↑' : '↓')}
                    </th>
                    <th onClick={() => {
                      setSortBy('date');
                      setSortOrder(sortBy === 'date' ? (sortOrder === 'asc' ? 'desc' : 'asc') : 'asc');
                    }} className="sortable-header">
                      DATE {sortBy === 'date' && (sortOrder === 'asc' ? '↑' : '↓')}
                    </th>
                    <th onClick={() => {
                      setSortBy('time');
                      setSortOrder(sortBy === 'time' ? (sortOrder === 'asc' ? 'desc' : 'asc') : 'asc');
                    }} className="sortable-header">
                      TIME {sortBy === 'time' && (sortOrder === 'asc' ? '↑' : '↓')}
                    </th>
                    <th onClick={() => {
                      setSortBy('location');
                      setSortOrder(sortBy === 'location' ? (sortOrder === 'asc' ? 'desc' : 'asc') : 'asc');
                    }} className="sortable-header">
                      LOCATION {sortBy === 'location' && (sortOrder === 'asc' ? '↑' : '↓')}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {filteredEvents.map(event => (
                    <tr key={getFieldValue(event, ['id', 'request_id'])}>
                      <td>{getFieldValue(event, ['activity', 'name', 'title', 'event_name'])}</td>
                      <td>{getFieldValue(event, ['date'])}</td>
                      <td>{getFieldValue(event, ['time'])}</td>
                      <td>{getFieldValue(event, ['venue', 'location', 'place'])}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <p className="no-events">No approved events found.</p>
          )}
        </div>
      </main>
    </div>
  );
}

export default AdminDashboard;